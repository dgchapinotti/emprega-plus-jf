<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirAdministrador();
$status=(string)($_GET['status']??'selecionado');
if(!in_array($status,['selecionado','contratado','cancelado'],true))$status='selecionado';
$rotulos=['selecionado'=>'Candidatos selecionados','contratado'=>'Candidatos contratados','cancelado'=>'Seleções canceladas'];
$consulta=$pdo->prepare(
    "SELECT se.status,se.selecionado_em,se.atualizado_em,c.nome_completo,c.telefone,
            uc.email AS candidato_email,cu.titulo_profissional,cid.nome AS cidade,cid.uf,
            e.razao_social,e.nome_fantasia,e.cnpj,ue.email AS empresa_email
     FROM selecoes_empresas se
     INNER JOIN candidatos c ON c.id=se.candidato_id
     INNER JOIN usuarios uc ON uc.id=c.usuario_id
     INNER JOIN cidades cid ON cid.id=c.cidade_id
     LEFT JOIN curriculos cu ON cu.candidato_id=c.id
     INNER JOIN empresas e ON e.id=se.empresa_id
     INNER JOIN usuarios ue ON ue.id=e.usuario_id
     WHERE se.status=?
     ORDER BY se.atualizado_em DESC,se.selecionado_em DESC"
);
$consulta->execute([$status]);$selecoes=$consulta->fetchAll();
$tituloPagina=$rotulos[$status];
require_once __DIR__.'/../includes/header.php';require_once __DIR__.'/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Resultados de empregabilidade</p><h1 class="h2 mb-1"><?= escapar($rotulos[$status]) ?></h1><p class="text-secondary mb-0"><?= count($selecoes) ?> ocorrência<?= count($selecoes)===1?'':'s' ?> registrada<?= count($selecoes)===1?'':'s' ?>.</p></div><a href="<?= url('admin/dashboard.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a></div>
    <div class="d-flex gap-2 mb-4"><?php foreach($rotulos as $chave=>$rotulo):?><a class="btn btn-sm <?= $status===$chave?'btn-primary':'btn-outline-primary' ?>" href="<?= url('admin/selecoes.php?status='.$chave) ?>"><?= escapar($rotulo) ?></a><?php endforeach;?></div>
    <?php if(!$selecoes):?><div class="alert alert-info">Nenhum registro com este status até o momento.</div><?php else:?><div class="row g-4"><?php foreach($selecoes as $s):$cnpj=preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/','$1.$2.$3/$4-$5',$s['cnpj']);?><div class="col-lg-6"><article class="card shadow-sm border-0 h-100"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3"><div><h2 class="h5 mb-1"><?= escapar($s['nome_completo']) ?></h2><p class="text-primary fw-semibold"><?= escapar((string)$s['titulo_profissional']) ?></p></div><span class="badge align-self-start <?= $status==='contratado'?'text-bg-success':($status==='selecionado'?'text-bg-primary':'text-bg-secondary') ?>"><?= escapar(ucfirst($status)) ?></span></div><p class="mb-1"><i class="fa-solid fa-location-dot me-2 text-secondary"></i><?= escapar($s['cidade'].' - '.$s['uf']) ?></p><p class="small text-secondary mb-3"><?= escapar($s['candidato_email']) ?> · <?= escapar($s['telefone']) ?></p><hr><p class="mb-1"><strong>Empresa:</strong> <?= escapar((string)($s['nome_fantasia']?:$s['razao_social'])) ?></p><p class="small text-secondary mb-1">CNPJ <?= escapar((string)$cnpj) ?> · <?= escapar($s['empresa_email']) ?></p><p class="small text-secondary mb-0">Selecionado em <?= date('d/m/Y H:i',strtotime($s['selecionado_em'])) ?></p></div></article></div><?php endforeach;?></div><?php endif;?>
</main>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
