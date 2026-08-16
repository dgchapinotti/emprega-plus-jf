<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirEmpresa();

$niveis = [
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

$filtros = [
    'curso' => trim((string) ($_GET['curso'] ?? '')),
    'experiencia' => trim((string) ($_GET['experiencia'] ?? '')),
    'cidade_id' => filter_var($_GET['cidade_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
    'area_id' => filter_var($_GET['area_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
    'nivel' => trim((string) ($_GET['nivel'] ?? '')),
    'competencia_id' => filter_var($_GET['competencia_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
];

if (!array_key_exists($filtros['nivel'], $niveis)) {
    $filtros['nivel'] = '';
}

$pagina = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$porPagina = 9;
$condicoes = ["u.status = 'ativo'", "u.perfil = 'candidato'", 'cu.visivel = 1'];
$parametros = [];

if ($filtros['curso'] !== '') {
    $condicoes[] = 'EXISTS (SELECT 1 FROM cursos cr WHERE cr.candidato_id = c.id AND cr.nome LIKE :curso)';
    $parametros['curso'] = '%' . $filtros['curso'] . '%';
}
if ($filtros['experiencia'] !== '') {
    $condicoes[] = 'EXISTS (SELECT 1 FROM experiencias ex WHERE ex.candidato_id = c.id AND ex.descricao LIKE :experiencia)';
    $parametros['experiencia'] = '%' . $filtros['experiencia'] . '%';
}
if ($filtros['cidade_id'] > 0) {
    $condicoes[] = 'c.cidade_id = :cidade_id';
    $parametros['cidade_id'] = $filtros['cidade_id'];
}
if ($filtros['area_id'] > 0) {
    $condicoes[] = 'cu.area_profissional_id = :area_id';
    $parametros['area_id'] = $filtros['area_id'];
}
if ($filtros['nivel'] !== '') {
    $condicoes[] = 'EXISTS (SELECT 1 FROM formacoes f WHERE f.candidato_id = c.id AND f.nivel = :nivel)';
    $parametros['nivel'] = $filtros['nivel'];
}
if ($filtros['competencia_id'] > 0) {
    $condicoes[] = 'EXISTS (SELECT 1 FROM candidato_competencias cc WHERE cc.candidato_id = c.id AND cc.competencia_id = :competencia_id)';
    $parametros['competencia_id'] = $filtros['competencia_id'];
}

$where = implode(' AND ', $condicoes);
$consultaTotal = $pdo->prepare("SELECT COUNT(*) FROM curriculos cu INNER JOIN candidatos c ON c.id = cu.candidato_id INNER JOIN usuarios u ON u.id = c.usuario_id WHERE {$where}");
$consultaTotal->execute($parametros);
$total = (int) $consultaTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT c.id AS candidato_id, c.nome_completo, cid.nome AS cidade, cid.uf,
               cu.titulo_profissional, cu.resumo_profissional, cu.disponibilidade,
               ap.nome AS area_profissional,
               EXISTS(SELECT 1 FROM experiencias et WHERE et.candidato_id=c.id AND et.emprego_atual=1) AS trabalhando_atualmente,
               (SELECT f.nivel FROM formacoes f WHERE f.candidato_id=c.id ORDER BY f.cursando DESC, COALESCE(f.data_conclusao,'9999-12-31') DESC, f.id DESC LIMIT 1) AS nivel_formacao,
               (SELECT GROUP_CONCAT(co.nome ORDER BY co.nome SEPARATOR ', ') FROM candidato_competencias cc INNER JOIN competencias co ON co.id=cc.competencia_id WHERE cc.candidato_id=c.id) AS competencias
        FROM curriculos cu
        INNER JOIN candidatos c ON c.id=cu.candidato_id
        INNER JOIN usuarios u ON u.id=c.usuario_id
        INNER JOIN cidades cid ON cid.id=c.cidade_id
        LEFT JOIN areas_profissionais ap ON ap.id=cu.area_profissional_id
        WHERE {$where}
        ORDER BY cu.atualizado_em DESC, c.nome_completo
        LIMIT :limite OFFSET :offset";
$consulta = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $consulta->bindValue(':' . $chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$consulta->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$consulta->bindValue(':offset', $offset, PDO::PARAM_INT);
$consulta->execute();
$candidatos = $consulta->fetchAll();

$cidades = $pdo->query(
    "SELECT cid.id, cid.nome, cid.uf, COUNT(DISTINCT c.id) AS total_candidatos
     FROM cidades cid
     INNER JOIN candidatos c ON c.cidade_id = cid.id
     INNER JOIN usuarios u ON u.id = c.usuario_id
     INNER JOIN curriculos cu ON cu.candidato_id = c.id
     WHERE cu.visivel = 1 AND u.status = 'ativo' AND u.perfil = 'candidato'
     GROUP BY cid.id, cid.nome, cid.uf
     ORDER BY cid.nome"
)->fetchAll();
$areas = $pdo->query('SELECT id,nome FROM areas_profissionais WHERE ativo=1 ORDER BY nome')->fetchAll();
$competencias = $pdo->query('SELECT id,nome FROM competencias WHERE ativo=1 ORDER BY nome')->fetchAll();

$tituloPagina = 'Pesquisar profissionais';
$mensagemErro = obterFlash('erro');
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';

function urlPaginaPesquisa(int $numero): string
{
    $query = $_GET;
    $query['pagina'] = $numero;
    return url('empresa/pesquisar.php') . '?' . http_build_query($query);
}
?>

<main class="container py-5">
    <?php if ($mensagemErro): ?><div class="alert alert-danger" role="alert"><?= escapar((string)$mensagemErro) ?></div><?php endif; ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Banco de currículos</p><h1 class="h2 mb-1">Pesquisa de profissionais</h1><p class="text-secondary mb-0">Encontre talentos de acordo com as necessidades da sua empresa.</p></div>
        <a href="<?= url('empresa/painel.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a>
    </div>

    <div class="card shadow-sm border-0 mb-4"><div class="card-body p-4">
        <form method="get" action="<?= url('empresa/pesquisar.php') ?>">
            <div class="row g-3">
                <div class="col-md-6"><label for="curso" class="form-label">Nome do curso complementar</label><input type="search" id="curso" name="curso" class="form-control" maxlength="100" placeholder="Ex.: Excel, Power BI, primeiros socorros" value="<?= escapar($filtros['curso']) ?>"></div>
                <div class="col-md-6"><label for="experiencia" class="form-label">Palavra-chave nas atividades profissionais</label><input type="search" id="experiencia" name="experiencia" class="form-control" maxlength="100" placeholder="Ex.: atendimento, estoque, manutenção" value="<?= escapar($filtros['experiencia']) ?>"></div>
                <div class="col-md-6 col-lg-3"><label for="cidade_id" class="form-label">Cidade</label><select id="cidade_id" name="cidade_id" class="form-select"><option value="">Todas (<?= array_sum(array_column($cidades, 'total_candidatos')) ?>)</option><?php foreach ($cidades as $cidade): ?><option value="<?= (int)$cidade['id'] ?>" <?= $filtros['cidade_id']===(int)$cidade['id']?'selected':'' ?>><?= escapar($cidade['nome'].' - '.$cidade['uf'].' ('.$cidade['total_candidatos'].')') ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6 col-lg-3"><label for="area_id" class="form-label">Área profissional</label><select id="area_id" name="area_id" class="form-select"><option value="">Todas</option><?php foreach ($areas as $area): ?><option value="<?= (int)$area['id'] ?>" <?= $filtros['area_id']===(int)$area['id']?'selected':'' ?>><?= escapar($area['nome']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label for="nivel" class="form-label">Escolaridade</label><select id="nivel" name="nivel" class="form-select"><option value="">Todas</option><?php foreach ($niveis as $valor=>$rotulo): ?><option value="<?= escapar($valor) ?>" <?= $filtros['nivel']===$valor?'selected':'' ?>><?= escapar($rotulo) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label for="competencia_id" class="form-label">Competência</label><select id="competencia_id" name="competencia_id" class="form-select"><option value="">Todas</option><?php foreach ($competencias as $competencia): ?><option value="<?= (int)$competencia['id'] ?>" <?= $filtros['competencia_id']===(int)$competencia['id']?'selected':'' ?>><?= escapar($competencia['nome']) ?></option><?php endforeach; ?></select></div>
                <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Pesquisar</button><a class="btn btn-outline-secondary" href="<?= url('empresa/pesquisar.php') ?>">Limpar filtros</a></div>
            </div>
        </form>
    </div></div>

    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0"><?= $total ?> profissional<?= $total===1?'':'is' ?> encontrado<?= $total===1?'':'s' ?></h2><span class="text-secondary small">Página <?= $pagina ?> de <?= $totalPaginas ?></span></div>

    <?php if (!$candidatos): ?>
        <div class="alert alert-info">Nenhum profissional corresponde aos filtros informados.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($candidatos as $candidato): ?>
                <div class="col-md-6 col-xl-4"><article class="card shadow-sm border-0 h-100"><div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3"><div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px"><i class="fa-solid fa-user-tie fa-lg"></i></div><div><h3 class="h5 mb-1"><?= escapar($candidato['nome_completo']) ?></h3><p class="text-primary fw-semibold mb-0"><?= escapar($candidato['titulo_profissional']) ?></p></div></div>
                    <p class="small text-secondary mb-2"><i class="fa-solid fa-location-dot me-2"></i><?= escapar($candidato['cidade'].' - '.$candidato['uf']) ?></p>
                    <p class="small mb-2"><?php if ($candidato['trabalhando_atualmente']): ?><span class="badge text-bg-success"><i class="fa-solid fa-briefcase me-1"></i>Trabalhando atualmente</span><?php else: ?><span class="badge text-bg-secondary"><i class="fa-solid fa-circle-check me-1"></i>Não está trabalhando atualmente</span><?php endif; ?></p>
                    <?php if ($candidato['area_profissional']): ?><p class="small mb-2"><span class="badge text-bg-light border"><?= escapar($candidato['area_profissional']) ?></span></p><?php endif; ?>
                    <?php if ($candidato['nivel_formacao']): ?><p class="small text-secondary mb-2"><i class="fa-solid fa-graduation-cap me-2"></i><?= escapar($niveis[$candidato['nivel_formacao']] ?? $candidato['nivel_formacao']) ?></p><?php endif; ?>
                    <?php if ($candidato['competencias']): ?><p class="small text-secondary mb-3"><strong>Competências:</strong> <?= escapar($candidato['competencias']) ?></p><?php endif; ?>
                    <a class="btn btn-outline-primary mt-auto" href="<?= url('empresa/candidato.php?id='.(int)$candidato['candidato_id']) ?>">Ver currículo completo</a>
                </div></article></div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPaginas > 1): ?><nav class="mt-4" aria-label="Paginação"><ul class="pagination justify-content-center"><li class="page-item <?= $pagina<=1?'disabled':'' ?>"><a class="page-link" href="<?= escapar(urlPaginaPesquisa(max(1,$pagina-1))) ?>">Anterior</a></li><?php for($p=max(1,$pagina-2);$p<=min($totalPaginas,$pagina+2);$p++): ?><li class="page-item <?= $p===$pagina?'active':'' ?>"><a class="page-link" href="<?= escapar(urlPaginaPesquisa($p)) ?>"><?= $p ?></a></li><?php endfor; ?><li class="page-item <?= $pagina >= $totalPaginas?'disabled':'' ?>"><a class="page-link" href="<?= escapar(urlPaginaPesquisa(min($totalPaginas,$pagina+1))) ?>">Próxima</a></li></ul></nav><?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
