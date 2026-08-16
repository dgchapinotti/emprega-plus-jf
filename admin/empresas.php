<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirAdministrador();

$busca = trim((string)($_GET['busca'] ?? ''));
$status = (string)($_GET['status'] ?? '');
$statusValidos = ['pendente','ativo','bloqueado','inativo'];
if (!in_array($status, $statusValidos, true)) $status = '';

$condicoes = ["u.perfil='empresa'"];
$parametros = [];
if ($busca !== '') {
    $condicoes[] = '(e.razao_social LIKE :b1 OR e.nome_fantasia LIKE :b2 OR e.cnpj LIKE :b3 OR u.email LIKE :b4 OR e.responsavel_nome LIKE :b5)';
    for ($i=1;$i<=5;$i++) $parametros['b'.$i] = '%'.$busca.'%';
}
if ($status !== '') {
    $condicoes[] = 'u.status=:status';
    $parametros['status'] = $status;
}

$sql = "SELECT e.id,e.cnpj,e.razao_social,e.nome_fantasia,e.telefone,e.responsavel_nome,
               e.aprovada_em,e.criado_em,u.id AS usuario_id,u.email,u.status,
               cid.nome AS cidade,cid.uf,aprovador.nome AS aprovador_nome,
               (SELECT COUNT(*) FROM selecoes_empresas se WHERE se.empresa_id=e.id AND se.status IN ('selecionado','contratado')) AS total_selecoes
        FROM empresas e
        INNER JOIN usuarios u ON u.id=e.usuario_id
        INNER JOIN cidades cid ON cid.id=e.cidade_id
        LEFT JOIN usuarios aprovador ON aprovador.id=e.aprovada_por
        WHERE ".implode(' AND ',$condicoes)."
        ORDER BY (u.status='pendente') DESC,e.criado_em DESC,e.razao_social";
$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$empresas = $consulta->fetchAll();

$totais = ['todas'=>0,'pendente'=>0,'ativo'=>0,'bloqueado'=>0,'inativo'=>0];
foreach($pdo->query("SELECT u.status,COUNT(*) total FROM empresas e INNER JOIN usuarios u ON u.id=e.usuario_id WHERE u.perfil='empresa' GROUP BY u.status") as $linha){$totais[$linha['status']] = (int)$linha['total'];$totais['todas'] += (int)$linha['total'];}

$tituloPagina = 'Gestão de empresas';
$mensagemSucesso = obterFlash('sucesso');
$mensagemErro = obterFlash('erro');
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Gestão municipal</p><h1 class="h2 mb-1">Empresas cadastradas</h1><p class="text-secondary mb-0">Analise e controle quem pode acessar o banco de currículos.</p></div><a href="<?= url('admin/dashboard.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a></div>
    <?php if($mensagemSucesso):?><div class="alert alert-success"><?= escapar((string)$mensagemSucesso) ?></div><?php endif;?>
    <?php if($mensagemErro):?><div class="alert alert-danger"><?= escapar((string)$mensagemErro) ?></div><?php endif;?>

    <div class="row g-3 mb-4">
        <?php foreach(['todas'=>'Todas','pendente'=>'Pendentes','ativo'=>'Ativas','bloqueado'=>'Bloqueadas'] as $chave=>$rotulo): ?><div class="col-6 col-lg-3"><a class="card shadow-sm border-0 text-decoration-none h-100 <?= ($status===$chave||($chave==='todas'&&$status===''))?'bg-primary text-white':'' ?>" href="<?= url('admin/empresas.php'.($chave==='todas'?'':'?status='.$chave)) ?>"><div class="card-body p-3"><p class="mb-1"><?= escapar($rotulo) ?></p><strong class="fs-3"><?= (int)$totais[$chave] ?></strong></div></a></div><?php endforeach; ?>
    </div>

    <div class="card shadow-sm border-0 mb-4"><div class="card-body p-4"><form method="get" class="row g-3 align-items-end"><div class="col-md-7"><label for="busca" class="form-label">Empresa, CNPJ, responsável ou e-mail</label><input type="search" id="busca" name="busca" class="form-control" value="<?= escapar($busca) ?>" placeholder="Digite para pesquisar"></div><div class="col-md-3"><label for="status" class="form-label">Status</label><select id="status" name="status" class="form-select"><option value="">Todos</option><?php foreach($statusValidos as $valor):?><option value="<?= escapar($valor) ?>" <?= $status===$valor?'selected':'' ?>><?= escapar(ucfirst($valor)) ?></option><?php endforeach;?></select></div><div class="col-md-2 d-grid"><button class="btn btn-primary">Pesquisar</button></div></form></div></div>

    <?php if(!$empresas):?><div class="alert alert-info">Nenhuma empresa encontrada.</div><?php endif;?>
    <div class="row g-4">
        <?php foreach($empresas as $empresa): $cnpj=preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/','$1.$2.$3/$4-$5',$empresa['cnpj']); ?>
        <div class="col-lg-6"><article class="card shadow-sm border-0 h-100"><div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h2 class="h5 mb-1"><?= escapar((string)($empresa['nome_fantasia'] ?: $empresa['razao_social'])) ?></h2><p class="text-secondary mb-0"><?= escapar($empresa['razao_social']) ?></p></div><span class="badge <?= ['ativo'=>'text-bg-success','pendente'=>'text-bg-warning','bloqueado'=>'text-bg-danger','inativo'=>'text-bg-secondary'][$empresa['status']] ?>"><?= escapar(ucfirst($empresa['status'])) ?></span></div>
            <dl class="row small mb-3"><dt class="col-sm-4">CNPJ</dt><dd class="col-sm-8"><?= escapar((string)$cnpj) ?></dd><dt class="col-sm-4">Responsável</dt><dd class="col-sm-8"><?= escapar($empresa['responsavel_nome']) ?></dd><dt class="col-sm-4">Contato</dt><dd class="col-sm-8"><?= escapar($empresa['email']) ?><br><?= escapar($empresa['telefone']) ?></dd><dt class="col-sm-4">Cidade</dt><dd class="col-sm-8"><?= escapar($empresa['cidade'].' - '.$empresa['uf']) ?></dd><dt class="col-sm-4">Seleções</dt><dd class="col-sm-8"><?= (int)$empresa['total_selecoes'] ?></dd></dl>
            <?php if($empresa['aprovada_em']):?><p class="small text-secondary">Aprovada em <?= date('d/m/Y H:i',strtotime($empresa['aprovada_em'])) ?><?= $empresa['aprovador_nome']?' por '.escapar($empresa['aprovador_nome']):'' ?>.</p><?php endif;?>
            <div class="d-flex flex-wrap gap-2 mt-auto">
                <?php if($empresa['status']==='pendente'):?><form action="<?= url('actions/alterar_status_empresa.php') ?>" method="post"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="empresa_id" value="<?= (int)$empresa['id'] ?>"><input type="hidden" name="acao" value="aprovar"><button class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Aprovar empresa</button></form><?php endif;?>
                <?php if($empresa['status']==='ativo'):?><form action="<?= url('actions/alterar_status_empresa.php') ?>" method="post" onsubmit="return confirm('Bloquear o acesso desta empresa?');"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="empresa_id" value="<?= (int)$empresa['id'] ?>"><input type="hidden" name="acao" value="bloquear"><button class="btn btn-outline-danger">Bloquear acesso</button></form><?php endif;?>
                <?php if(in_array($empresa['status'],['bloqueado','inativo'],true)):?><form action="<?= url('actions/alterar_status_empresa.php') ?>" method="post"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="empresa_id" value="<?= (int)$empresa['id'] ?>"><input type="hidden" name="acao" value="reativar"><button class="btn btn-outline-success">Reativar acesso</button></form><?php endif;?>
            </div>
        </div></article></div>
        <?php endforeach;?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
