<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$candidatoId = obterCandidatoId($pdo);

$consulta = $pdo->prepare(
    "SELECT c.*, u.email, cid.nome AS cidade, cid.uf,
            cu.titulo_profissional, cu.objetivo_profissional,
            cu.resumo_profissional, cu.disponibilidade,
            cu.pretensao_salarial, ap.nome AS area_profissional
     FROM candidatos c
     INNER JOIN usuarios u ON u.id = c.usuario_id
     LEFT JOIN cidades cid ON cid.id = c.cidade_id
     LEFT JOIN curriculos cu ON cu.candidato_id = c.id
     LEFT JOIN areas_profissionais ap ON ap.id = cu.area_profissional_id
     WHERE c.id = ? LIMIT 1"
);
$consulta->execute([$candidatoId]);
$candidato = $consulta->fetch();

if (!$candidato) {
    definirFlash('erro', 'Não foi possível localizar os dados do seu currículo.');
    redirecionar('candidato/painel.php');
}

$buscar = static function (PDO $pdo, string $sql, int $id): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll();
};

$formacoes = $buscar($pdo, "SELECT * FROM formacoes WHERE candidato_id=? ORDER BY cursando DESC, COALESCE(data_conclusao,'9999-12-31') DESC", $candidatoId);
$cursos = $buscar($pdo, 'SELECT * FROM cursos WHERE candidato_id=? ORDER BY ano_conclusao DESC, nome', $candidatoId);
$experiencias = $buscar($pdo, 'SELECT * FROM experiencias WHERE candidato_id=? ORDER BY emprego_atual DESC, data_inicio DESC', $candidatoId);
$competencias = $buscar($pdo, 'SELECT co.nome, cc.nivel FROM candidato_competencias cc INNER JOIN competencias co ON co.id=cc.competencia_id WHERE cc.candidato_id=? ORDER BY co.nome', $candidatoId);
$idiomas = $buscar($pdo, 'SELECT i.nome, ci.nivel FROM candidato_idiomas ci INNER JOIN idiomas i ON i.id=ci.idioma_id WHERE ci.candidato_id=? ORDER BY i.nome', $candidatoId);

$estaTrabalhando = count(array_filter(
    $experiencias,
    static fn(array $experiencia): bool => (bool) $experiencia['emprego_atual']
)) > 0;

$etapasConcluidas = 0;
$etapasConcluidas += !empty($candidato['telefone']) && !empty($candidato['cep']) && !empty($candidato['logradouro']) ? 1 : 0;
$etapasConcluidas += $formacoes ? 1 : 0;
$etapasConcluidas += $cursos ? 1 : 0;
$etapasConcluidas += $experiencias ? 1 : 0;
$percentual = $etapasConcluidas * 25;

$idade = !empty($candidato['data_nascimento'])
    ? (new DateTimeImmutable($candidato['data_nascimento']))->diff(new DateTimeImmutable('today'))->y
    : null;
$cpf = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', somenteDigitos((string) $candidato['cpf']));
$rotulosNivel = [
    'fundamental_incompleto' => 'Ensino Fundamental incompleto',
    'fundamental_completo' => 'Ensino Fundamental completo',
    'medio_incompleto' => 'Ensino Médio incompleto',
    'medio_completo' => 'Ensino Médio completo',
    'tecnico' => 'Curso técnico',
    'superior_incompleto' => 'Ensino Superior incompleto',
    'superior_completo' => 'Ensino Superior completo',
    'pos_graduacao' => 'Pós-graduação',
    'mestrado' => 'Mestrado',
    'doutorado' => 'Doutorado',
];

$tituloPagina = 'Meu currículo';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1"><?= escapar((string) ($candidato['area_profissional'] ?: 'Meu currículo digital')) ?></p>
            <h1 class="h2 mb-1"><?= escapar($candidato['nome_completo']) ?></h1>
            <?php if (!empty($candidato['titulo_profissional'])): ?><p class="lead mb-2"><?= escapar($candidato['titulo_profissional']) ?></p><?php endif; ?>
            <?php if ($estaTrabalhando): ?>
                <span class="badge text-bg-success"><i class="fa-solid fa-briefcase me-1"></i>Trabalhando atualmente</span>
            <?php else: ?>
                <span class="badge text-bg-secondary"><i class="fa-solid fa-circle-check me-1"></i>Não está trabalhando atualmente</span>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <a href="<?= url('candidato/baixar-curriculo.php') ?>" class="btn btn-success"><i class="fa-solid fa-file-pdf me-2" aria-hidden="true"></i>Baixar currículo em PDF</a>
            <a href="<?= url('candidato/painel.php') ?>" class="btn btn-outline-secondary">Voltar ao painel</a>
        </div>
    </div>

    <div class="alert alert-info" role="status">
        <i class="fa-solid fa-eye me-2" aria-hidden="true"></i>Esta é a visualização do seu próprio currículo com os dados cadastrados até agora.
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><i class="fa-solid fa-user text-primary me-2"></i>Perfil profissional</h2>
                    <?php if (!empty($candidato['objetivo_profissional'])): ?><h3 class="h6">Objetivo</h3><p><?= nl2br(escapar($candidato['objetivo_profissional'])) ?></p><?php endif; ?>
                    <?php if (!empty($candidato['resumo_profissional'])): ?><h3 class="h6">Resumo</h3><p class="mb-0"><?= nl2br(escapar($candidato['resumo_profissional'])) ?></p><?php endif; ?>
                    <?php if (empty($candidato['objetivo_profissional']) && empty($candidato['resumo_profissional'])): ?><p class="text-secondary mb-0">Seu perfil profissional ainda está em construção.</p><?php endif; ?>
                </div>
            </section>

            <section class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><i class="fa-solid fa-briefcase text-primary me-2"></i>Experiências profissionais</h2>
                    <?php if (!$experiencias): ?><p class="text-secondary mb-0">Nenhuma experiência informada.</p><?php endif; ?>
                    <?php foreach ($experiencias as $experiencia): ?>
                        <div class="border-start border-primary border-3 ps-3 mb-4">
                            <h3 class="h6 mb-1"><?= escapar($experiencia['cargo']) ?></h3>
                            <p class="fw-semibold mb-1"><?= escapar($experiencia['empresa']) ?></p>
                            <p class="small text-secondary mb-2"><?= date('m/Y', strtotime($experiencia['data_inicio'])) ?> — <?= $experiencia['emprego_atual'] ? 'Atual' : (!empty($experiencia['data_fim']) ? date('m/Y', strtotime($experiencia['data_fim'])) : 'Não informado') ?></p>
                            <?php if (!empty($experiencia['descricao'])): ?><p class="mb-0"><?= nl2br(escapar($experiencia['descricao'])) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Formações e cursos</h2>
                    <?php if (!$formacoes): ?><p class="text-secondary">Nenhuma formação informada.</p><?php endif; ?>
                    <?php foreach ($formacoes as $formacao): ?>
                        <div class="mb-3">
                            <h3 class="h6 mb-1"><?= escapar((string) ($formacao['curso'] ?: ($rotulosNivel[$formacao['nivel']] ?? $formacao['nivel']))) ?></h3>
                            <p class="mb-1"><?= escapar($formacao['instituicao']) ?></p>
                            <p class="small text-secondary mb-0"><?= escapar($rotulosNivel[$formacao['nivel']] ?? $formacao['nivel']) ?> · <?= $formacao['cursando'] ? 'Cursando' : (!empty($formacao['data_conclusao']) ? 'Concluído em ' . date('Y', strtotime($formacao['data_conclusao'])) : 'Conclusão não informada') ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($cursos): ?><hr><h3 class="h6">Cursos complementares</h3><ul class="mb-0"><?php foreach ($cursos as $curso): ?><li><?= escapar($curso['nome']) ?> — <?= escapar((string) $curso['instituicao']) ?><?= $curso['carga_horaria'] ? ' (' . (int) $curso['carga_horaria'] . 'h)' : '' ?><?= $curso['ano_conclusao'] ? ' · ' . (int) $curso['ano_conclusao'] : '' ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if (!$cursos): ?><hr><p class="text-secondary mb-0">Nenhum curso complementar informado.</p><?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Preenchimento do currículo</h2>
                    <div class="progress mb-2" role="progressbar" aria-label="Progresso do currículo" aria-valuenow="<?= $percentual ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: <?= $percentual ?>%"><?= $percentual ?>%</div></div>
                    <p class="small text-secondary mb-0"><?= $etapasConcluidas ?> de 4 etapas principais preenchidas.</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Dados pessoais</h2>
                    <p><i class="fa-solid fa-id-card text-primary me-2"></i><?= escapar((string) $cpf) ?></p>
                    <?php if ($idade !== null): ?><p><i class="fa-solid fa-user text-primary me-2"></i><?= $idade ?> anos</p><?php endif; ?>
                    <p><i class="fa-solid fa-phone text-primary me-2"></i><?= escapar((string) $candidato['telefone']) ?></p>
                    <p><i class="fa-solid fa-envelope text-primary me-2"></i><span class="text-break"><?= escapar($candidato['email']) ?></span></p>
                    <p class="mb-1"><i class="fa-solid fa-location-dot text-primary me-2"></i><?= escapar(trim((string) $candidato['logradouro'] . ', ' . (string) $candidato['numero'], ', ')) ?></p>
                    <?php if (!empty($candidato['complemento'])): ?><p class="ms-4 mb-1"><?= escapar($candidato['complemento']) ?></p><?php endif; ?>
                    <p class="ms-4 mb-0"><?= escapar(trim((string) $candidato['bairro'] . ' · ' . (string) $candidato['cidade'] . ' - ' . (string) $candidato['uf'], ' ·-')) ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Competências</h2>
                    <?php if ($competencias): ?><div class="d-flex flex-wrap gap-2 mb-4"><?php foreach ($competencias as $competencia): ?><span class="badge text-bg-primary"><?= escapar($competencia['nome']) ?></span><?php endforeach; ?></div><?php else: ?><p class="text-secondary">Nenhuma competência informada.</p><?php endif; ?>
                    <?php if ($idiomas): ?><h3 class="h6">Idiomas</h3><ul class="mb-0"><?php foreach ($idiomas as $idioma): ?><li><?= escapar($idioma['nome']) ?> — <?= escapar(ucfirst($idioma['nivel'])) ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if (!empty($candidato['disponibilidade'])): ?><hr><p class="mb-0"><strong>Disponibilidade:</strong> <?= escapar($candidato['disponibilidade']) ?></p><?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
