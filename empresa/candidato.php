<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirEmpresa();
$empresaId = obterEmpresaId($pdo);
$candidatoId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

$consulta = $pdo->prepare(
    "SELECT c.id,c.nome_completo,c.data_nascimento,c.telefone,u.email,cid.nome AS cidade,cid.uf,
            cu.titulo_profissional,cu.objetivo_profissional,cu.resumo_profissional,cu.disponibilidade,cu.pretensao_salarial,
            ap.nome AS area_profissional
     FROM candidatos c
     INNER JOIN usuarios u ON u.id=c.usuario_id AND u.status='ativo'
     INNER JOIN cidades cid ON cid.id=c.cidade_id
     INNER JOIN curriculos cu ON cu.candidato_id=c.id AND cu.visivel=1
     LEFT JOIN areas_profissionais ap ON ap.id=cu.area_profissional_id
     WHERE c.id=? LIMIT 1"
);
$consulta->execute([$candidatoId]);
$candidato = $consulta->fetch();

if (!$candidato) {
    definirFlash('erro', 'Currículo não encontrado ou indisponível.');
    redirecionar('empresa/pesquisar.php');
}

$buscar = static function (PDO $pdo, string $sql, int $id): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll();
};
$formacoes = $buscar($pdo, 'SELECT * FROM formacoes WHERE candidato_id=? ORDER BY cursando DESC, COALESCE(data_conclusao,\'9999-12-31\') DESC', $candidatoId);
$cursos = $buscar($pdo, 'SELECT * FROM cursos WHERE candidato_id=? ORDER BY ano_conclusao DESC,nome', $candidatoId);
$experiencias = $buscar($pdo, 'SELECT * FROM experiencias WHERE candidato_id=? ORDER BY emprego_atual DESC,data_inicio DESC', $candidatoId);
$estaTrabalhando = count(array_filter($experiencias, static fn(array $experiencia): bool => (bool)$experiencia['emprego_atual'])) > 0;
$competencias = $buscar($pdo, 'SELECT co.nome,cc.nivel FROM candidato_competencias cc INNER JOIN competencias co ON co.id=cc.competencia_id WHERE cc.candidato_id=? ORDER BY co.nome', $candidatoId);
$idiomas = $buscar($pdo, 'SELECT i.nome,ci.nivel FROM candidato_idiomas ci INNER JOIN idiomas i ON i.id=ci.idioma_id WHERE ci.candidato_id=? ORDER BY i.nome', $candidatoId);
$consultaSelecao = $pdo->prepare('SELECT status, selecionado_em FROM selecoes_empresas WHERE empresa_id=? AND candidato_id=? LIMIT 1');
$consultaSelecao->execute([$empresaId, $candidatoId]);
$selecao = $consultaSelecao->fetch();
$estaSelecionado = $selecao && in_array($selecao['status'], ['selecionado', 'contratado'], true);
$mensagemSucesso = obterFlash('sucesso');
$mensagemErro = obterFlash('erro');
$idade = (new DateTimeImmutable($candidato['data_nascimento']))->diff(new DateTimeImmutable('today'))->y;
$rotulosNivel = ['fundamental_incompleto'=>'Ensino Fundamental incompleto','fundamental_completo'=>'Ensino Fundamental completo','medio_incompleto'=>'Ensino Médio incompleto','medio_completo'=>'Ensino Médio completo','tecnico'=>'Curso técnico','superior_incompleto'=>'Ensino Superior incompleto','superior_completo'=>'Ensino Superior completo','pos_graduacao'=>'Pós-graduação','mestrado'=>'Mestrado','doutorado'=>'Doutorado'];
$tituloPagina = 'Currículo de '.$candidato['nome_completo'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <?php if ($mensagemSucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string)$mensagemSucesso) ?></div><?php endif; ?>
    <?php if ($mensagemErro): ?><div class="alert alert-danger" role="alert"><?= escapar((string)$mensagemErro) ?></div><?php endif; ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1"><?= escapar((string)($candidato['area_profissional'] ?? 'Perfil profissional')) ?></p><h1 class="h2 mb-1"><?= escapar($candidato['nome_completo']) ?></h1><p class="lead mb-2"><?= escapar($candidato['titulo_profissional']) ?></p><?php if ($estaTrabalhando): ?><span class="badge text-bg-success"><i class="fa-solid fa-briefcase me-1"></i>Trabalhando atualmente</span><?php else: ?><span class="badge text-bg-secondary"><i class="fa-solid fa-circle-check me-1"></i>Não está trabalhando atualmente</span><?php endif; ?></div><div class="d-flex flex-wrap gap-2 align-self-start"><a href="<?= url('empresa/baixar-curriculo.php?id=' . (int) $candidatoId) ?>" class="btn btn-success"><i class="fa-solid fa-file-pdf me-2" aria-hidden="true"></i>Baixar currículo em PDF</a><a href="<?= url('empresa/pesquisar.php') ?>" class="btn btn-outline-secondary">Voltar à pesquisa</a></div></div>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="card shadow-sm border-0 mb-4"><div class="card-body p-4"><h2 class="h5 mb-3">Perfil profissional</h2><?php if($candidato['objetivo_profissional']): ?><h3 class="h6">Objetivo</h3><p><?= nl2br(escapar($candidato['objetivo_profissional'])) ?></p><?php endif; ?><?php if($candidato['resumo_profissional']): ?><h3 class="h6">Resumo</h3><p class="mb-0"><?= nl2br(escapar($candidato['resumo_profissional'])) ?></p><?php endif; ?></div></section>
            <section class="card shadow-sm border-0 mb-4"><div class="card-body p-4"><h2 class="h5 mb-3"><i class="fa-solid fa-briefcase text-primary me-2"></i>Experiências profissionais</h2><?php if(!$experiencias): ?><p class="text-secondary mb-0">Nenhuma experiência informada.</p><?php endif; ?><?php foreach($experiencias as $exp): ?><div class="border-start border-primary border-3 ps-3 mb-4"><h3 class="h6 mb-1"><?= escapar($exp['cargo']) ?></h3><p class="fw-semibold mb-1"><?= escapar($exp['empresa']) ?></p><p class="small text-secondary mb-2"><?= date('m/Y',strtotime($exp['data_inicio'])) ?> — <?= $exp['emprego_atual']?'Atual':date('m/Y',strtotime($exp['data_fim'])) ?></p><p class="mb-0"><?= nl2br(escapar((string)$exp['descricao'])) ?></p></div><?php endforeach; ?></div></section>
            <section class="card shadow-sm border-0"><div class="card-body p-4"><h2 class="h5 mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Formação e cursos</h2><?php foreach($formacoes as $f): ?><div class="mb-3"><h3 class="h6 mb-1"><?= escapar((string)($f['curso'] ?: ($rotulosNivel[$f['nivel']] ?? $f['nivel']))) ?></h3><p class="mb-1"><?= escapar($f['instituicao']) ?></p><p class="small text-secondary mb-0"><?= escapar($rotulosNivel[$f['nivel']] ?? $f['nivel']) ?> · <?= $f['cursando']?'Cursando':($f['data_conclusao']?'Concluído em '.date('Y',strtotime($f['data_conclusao'])):'') ?></p></div><?php endforeach; ?><?php if($cursos): ?><hr><h3 class="h6">Cursos complementares</h3><ul class="mb-0"><?php foreach($cursos as $curso): ?><li><?= escapar($curso['nome']) ?> — <?= escapar((string)$curso['instituicao']) ?><?= $curso['carga_horaria']?' ('.(int)$curso['carga_horaria'].'h)':'' ?></li><?php endforeach; ?></ul><?php endif; ?></div></section>
        </div>
        <aside class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 <?= $estaSelecionado ? 'border border-success' : '' ?>">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="fa-solid fa-user-check fa-2x <?= $estaSelecionado ? 'text-success' : 'text-primary' ?>" aria-hidden="true"></i>
                        <div><h2 class="h5 mb-1">Resultado da seleção</h2><p class="small text-secondary mb-0">Informe o andamento à Prefeitura.</p></div>
                    </div>
                    <form action="<?= url('actions/alterar_selecao_candidato.php') ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                        <input type="hidden" name="candidato_id" value="<?= (int)$candidatoId ?>">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="candidato_selecionado" <?= $estaSelecionado ? 'checked' : '' ?> disabled>
                            <label class="form-check-label fw-semibold" for="candidato_selecionado">
                                <?= $estaSelecionado ? 'Candidato selecionado pela empresa' : 'Ainda não selecionado' ?>
                            </label>
                        </div>
                        <?php if ($estaSelecionado): ?>
                            <input type="hidden" name="acao" value="cancelar">
                            <button type="submit" class="btn btn-outline-danger w-100">Remover seleção</button>
                            <?php if (!empty($selecao['selecionado_em'])): ?><p class="small text-secondary text-center mt-2 mb-0">Selecionado em <?= date('d/m/Y \à\s H:i', strtotime($selecao['selecionado_em'])) ?></p><?php endif; ?>
                        <?php else: ?>
                            <input type="hidden" name="acao" value="selecionar">
                            <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-check me-2" aria-hidden="true"></i>Marcar como selecionado</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm border-0 mb-4"><div class="card-body p-4"><h2 class="h5 mb-3">Contato</h2><p><i class="fa-solid fa-location-dot text-primary me-2"></i><?= escapar($candidato['cidade'].' - '.$candidato['uf']) ?></p><p><i class="fa-solid fa-user text-primary me-2"></i><?= $idade ?> anos</p><p><i class="fa-solid fa-phone text-primary me-2"></i><a href="tel:<?= escapar(somenteDigitos($candidato['telefone'])) ?>"><?= escapar($candidato['telefone']) ?></a></p><p class="mb-0"><i class="fa-solid fa-envelope text-primary me-2"></i><a class="text-break" href="mailto:<?= escapar($candidato['email']) ?>"><?= escapar($candidato['email']) ?></a></p></div></div>
            <div class="card shadow-sm border-0"><div class="card-body p-4"><h2 class="h5 mb-3">Competências</h2><div class="d-flex flex-wrap gap-2 mb-4"><?php foreach($competencias as $comp): ?><span class="badge text-bg-primary"><?= escapar($comp['nome']) ?></span><?php endforeach; ?></div><?php if($idiomas): ?><h3 class="h6">Idiomas</h3><ul class="mb-0"><?php foreach($idiomas as $idioma): ?><li><?= escapar($idioma['nome']) ?> — <?= escapar(ucfirst($idioma['nivel'])) ?></li><?php endforeach; ?></ul><?php endif; ?><?php if($candidato['disponibilidade']): ?><hr><p class="mb-0"><strong>Disponibilidade:</strong> <?= escapar($candidato['disponibilidade']) ?></p><?php endif; ?></div></div>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
