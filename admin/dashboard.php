<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirAdministrador();

$indicadores = [
    'candidatos' => (int)$pdo->query("SELECT COUNT(*) FROM candidatos c INNER JOIN usuarios u ON u.id=c.usuario_id WHERE u.status='ativo'")->fetchColumn(),
    'curriculos' => (int)$pdo->query('SELECT COUNT(*) FROM curriculos WHERE visivel=1')->fetchColumn(),
    'empresas' => (int)$pdo->query("SELECT COUNT(*) FROM empresas e INNER JOIN usuarios u ON u.id=e.usuario_id WHERE u.status='ativo'")->fetchColumn(),
    'pendentes' => (int)$pdo->query("SELECT COUNT(*) FROM empresas e INNER JOIN usuarios u ON u.id=e.usuario_id WHERE u.status='pendente'")->fetchColumn(),
    'selecionados' => (int)$pdo->query("SELECT COUNT(*) FROM selecoes_empresas WHERE status='selecionado'")->fetchColumn(),
    'contratados' => (int)$pdo->query("SELECT COUNT(*) FROM selecoes_empresas WHERE status='contratado'")->fetchColumn(),
    'novos_mes' => (int)$pdo->query("SELECT COUNT(*) FROM candidatos WHERE criado_em >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn(),
    'trabalhando' => (int)$pdo->query('SELECT COUNT(DISTINCT candidato_id) FROM experiencias WHERE emprego_atual=1')->fetchColumn(),
];

$tituloPagina = 'Painel da Prefeitura';
$mensagemSucesso = obterFlash('sucesso');
$mensagemErro = obterFlash('erro');
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <?php if ($mensagemSucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string)$mensagemSucesso) ?></div><?php endif; ?>
    <?php if ($mensagemErro): ?><div class="alert alert-danger" role="alert"><?= escapar((string)$mensagemErro) ?></div><?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Gestão municipal</p><h1 class="h2 mb-1">Painel da Prefeitura</h1><p class="text-secondary mb-0">Olá, <?= escapar((string)($_SESSION['nome'] ?? 'gestor')) ?>. Acompanhe os resultados do Emprega+.</p></div>
        <span class="badge <?= administradorMaster() ? 'text-bg-dark' : 'text-bg-primary' ?> fs-6 align-self-start"><i class="fa-solid fa-shield-halved me-1"></i><?= administradorMaster() ? 'Administrador master' : 'Administrador gestor' ?></span>
    </div>

    <div class="row g-4 mb-5">
        <?php
        $cards = [
            ['Candidatos ativos',$indicadores['candidatos'],'fa-users','primary','admin/candidatos.php?filtro=ativos'],
            ['Currículos visíveis',$indicadores['curriculos'],'fa-file-lines','success','admin/candidatos.php?filtro=curriculos'],
            ['Empresas ativas',$indicadores['empresas'],'fa-building','info','admin/empresas.php?status=ativo'],
            ['Empresas pendentes',$indicadores['pendentes'],'fa-clock','warning','admin/empresas.php?status=pendente'],
            ['Selecionados',$indicadores['selecionados'],'fa-user-check','primary','admin/selecoes.php?status=selecionado'],
            ['Contratados',$indicadores['contratados'],'fa-handshake','success','admin/selecoes.php?status=contratado'],
            ['Novos no mês',$indicadores['novos_mes'],'fa-calendar-plus','info','admin/candidatos.php?filtro=novos'],
            ['Trabalhando atualmente',$indicadores['trabalhando'],'fa-briefcase','secondary','admin/candidatos.php?filtro=trabalhando'],
        ];
        foreach ($cards as [$rotulo,$valor,$icone,$cor,$destino]): ?>
            <div class="col-sm-6 col-xl-3"><a class="card shadow-sm border-0 h-100 text-decoration-none text-body" href="<?= url($destino) ?>" title="Ver detalhes de <?= escapar($rotulo) ?>"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-start"><div><p class="text-secondary mb-1"><?= escapar($rotulo) ?></p><p class="display-6 fw-bold mb-0"><?= (int)$valor ?></p></div><i class="fa-solid <?= escapar($icone) ?> fa-2x text-<?= escapar($cor) ?>"></i></div><p class="small text-primary mt-3 mb-0">Ver detalhes <i class="fa-solid fa-arrow-right ms-1"></i></p></div></a></div>
        <?php endforeach; ?>
    </div>

    <h2 class="h4 mb-3">Gestão do sistema</h2>
    <div class="row g-4">
        <div class="col-md-6 col-xl-4"><div class="card shadow-sm border-0 h-100"><div class="card-body p-4 d-flex flex-column"><i class="fa-solid fa-building-circle-check fa-3x text-primary mb-3"></i><h3 class="h5">Empresas</h3><p class="text-secondary">Aprove, bloqueie e acompanhe as empresas cadastradas.</p><a class="btn btn-primary mt-auto" href="<?= url('admin/empresas.php') ?>">Gerenciar empresas</a></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card shadow-sm border-0 h-100"><div class="card-body p-4 d-flex flex-column"><i class="fa-solid fa-chart-pie fa-3x text-success mb-3"></i><h3 class="h5">Relatórios e gráficos</h3><p class="text-secondary">Analise cidades, escolaridade, áreas e resultados de seleção.</p><a class="btn btn-success mt-auto" href="<?= url('admin/relatorios.php') ?>">Abrir relatórios</a></div></div></div>
        <?php if (administradorMaster()): ?><div class="col-md-6 col-xl-4"><div class="card shadow-sm border-0 h-100"><div class="card-body p-4 d-flex flex-column"><i class="fa-solid fa-user-shield fa-3x text-dark mb-3"></i><h3 class="h5">Administradores</h3><p class="text-secondary">Conceda ou suspenda acessos de outros servidores da Prefeitura.</p><a class="btn btn-dark mt-auto" href="<?= url('admin/administradores.php') ?>">Gerenciar acessos</a></div></div></div><?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
