<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirAdministrador();

$filtro = (string)($_GET['filtro'] ?? 'ativos');
$filtrosValidos = ['ativos','curriculos','novos','trabalhando'];
if(!in_array($filtro,$filtrosValidos,true))$filtro='ativos';
$titulos=['ativos'=>'Candidatos ativos','curriculos'=>'Currículos visíveis','novos'=>'Novos candidatos no mês','trabalhando'=>'Candidatos trabalhando atualmente'];

$condicoes=["u.perfil='candidato'","u.status='ativo'"];
if($filtro==='curriculos')$condicoes[]='cu.visivel=1';
if($filtro==='novos')$condicoes[]="c.criado_em >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-01')";
if($filtro==='trabalhando')$condicoes[]='EXISTS(SELECT 1 FROM experiencias ex WHERE ex.candidato_id=c.id AND ex.emprego_atual=1)';

$sql="SELECT c.id,c.nome_completo,c.telefone,c.data_nascimento,c.criado_em,u.email,
             cid.nome AS cidade,cid.uf,cu.titulo_profissional,cu.visivel,
             ap.nome AS area_profissional,
             EXISTS(SELECT 1 FROM experiencias et WHERE et.candidato_id=c.id AND et.emprego_atual=1) AS trabalhando,
             (SELECT COUNT(*) FROM selecoes_empresas se WHERE se.candidato_id=c.id AND se.status IN ('selecionado','contratado')) AS selecoes
      FROM candidatos c
      INNER JOIN usuarios u ON u.id=c.usuario_id
      INNER JOIN cidades cid ON cid.id=c.cidade_id
      LEFT JOIN curriculos cu ON cu.candidato_id=c.id
      LEFT JOIN areas_profissionais ap ON ap.id=cu.area_profissional_id
      WHERE ".implode(' AND ',$condicoes)."
      ORDER BY c.nome_completo";
$candidatos=$pdo->query($sql)->fetchAll();
$tituloPagina=$titulos[$filtro];
require_once __DIR__.'/../includes/header.php';require_once __DIR__.'/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Detalhamento do indicador</p><h1 class="h2 mb-1"><?= escapar($titulos[$filtro]) ?></h1><p class="text-secondary mb-0"><?= count($candidatos) ?> registro<?= count($candidatos)===1?'':'s' ?> encontrado<?= count($candidatos)===1?'':'s' ?>.</p></div><a href="<?= url('admin/dashboard.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a></div>
    <div class="d-flex flex-wrap gap-2 mb-4"><?php foreach($titulos as $chave=>$rotulo):?><a class="btn btn-sm <?= $filtro===$chave?'btn-primary':'btn-outline-primary' ?>" href="<?= url('admin/candidatos.php?filtro='.$chave) ?>"><?= escapar($rotulo) ?></a><?php endforeach;?></div>
    <?php if(!$candidatos):?><div class="alert alert-info">Nenhum candidato neste indicador.</div><?php else:?><div class="card shadow-sm border-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Candidato</th><th>Perfil</th><th>Cidade</th><th>Situação</th><th>Seleções</th></tr></thead><tbody><?php foreach($candidatos as $c):?><tr><td><strong><?= escapar($c['nome_completo']) ?></strong><br><small class="text-secondary"><?= escapar($c['email']) ?><br><?= escapar($c['telefone']) ?></small></td><td><?= escapar((string)($c['titulo_profissional']?:'Currículo ainda não publicado')) ?><?php if($c['area_profissional']):?><br><small class="text-secondary"><?= escapar($c['area_profissional']) ?></small><?php endif;?></td><td><?= escapar($c['cidade'].' - '.$c['uf']) ?></td><td><?php if($c['trabalhando']):?><span class="badge text-bg-success">Trabalhando</span><?php else:?><span class="badge text-bg-secondary">Sem vínculo atual</span><?php endif;?> <?php if($c['visivel']):?><span class="badge text-bg-primary">Currículo visível</span><?php endif;?></td><td class="text-center"><span class="badge text-bg-dark"><?= (int)$c['selecoes'] ?></span></td></tr><?php endforeach;?></tbody></table></div></div><?php endif;?>
</main>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
