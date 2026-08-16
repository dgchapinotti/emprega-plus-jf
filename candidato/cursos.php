<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
$candidatoId = obterCandidatoId($pdo);
$editarId = filter_var($_GET['editar'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$cursoEditado = null;
if ($editarId) { $q=$pdo->prepare('SELECT * FROM cursos WHERE id=? AND candidato_id=? LIMIT 1'); $q->execute([$editarId,$candidatoId]); $cursoEditado=$q->fetch() ?: null; }
$q=$pdo->prepare('SELECT * FROM cursos WHERE candidato_id=? ORDER BY ano_conclusao DESC, id DESC'); $q->execute([$candidatoId]); $cursos=$q->fetchAll();
$sucesso=obterFlash('sucesso'); $erros=obterFlash('erros',[]); $tituloPagina='Cursos complementares';
require_once __DIR__ . '/../includes/header.php'; require_once __DIR__ . '/../includes/menu.php';
?>
<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Currículo digital</p><h1 class="h2 mb-1">Cursos complementares</h1><p class="text-secondary mb-0">Registre cursos, capacitações e certificações.</p></div><a href="<?= url('candidato/painel.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a></div>
    <?php if($sucesso):?><div class="alert alert-success"><?=escapar((string)$sucesso)?></div><?php endif;?>
    <?php if($erros):?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($erros as $erro):?><li><?=escapar((string)$erro)?></li><?php endforeach;?></ul></div><?php endif;?>
    <div class="row g-4">
        <div class="col-lg-5"><div class="card shadow-sm border-0"><div class="card-body p-4"><h2 class="h5 mb-3"><?=$cursoEditado?'Editar curso':'Adicionar curso'?></h2>
            <form action="<?=url('actions/salvar_curso.php')?>" method="post"><input type="hidden" name="csrf_token" value="<?=escapar(tokenCsrf())?>"><input type="hidden" name="id" value="<?=(int)($cursoEditado['id']??0)?>">
                <div class="mb-3"><label for="nome_curso" class="form-label">Nome do curso</label><input type="text" id="nome_curso" name="nome" class="form-control" maxlength="180" required value="<?=escapar((string)($cursoEditado['nome']??''))?>"></div>
                <div class="mb-3"><label for="instituicao_curso" class="form-label">Instituição</label><input type="text" id="instituicao_curso" name="instituicao" class="form-control" maxlength="180" required value="<?=escapar((string)($cursoEditado['instituicao']??''))?>"></div>
                <div class="row g-3 mb-4"><div class="col-md-6"><label for="carga_horaria" class="form-label">Carga horária</label><div class="input-group"><input type="number" id="carga_horaria" name="carga_horaria" class="form-control" min="1" max="65535" required value="<?=escapar((string)($cursoEditado['carga_horaria']??''))?>"><span class="input-group-text">horas</span></div></div><div class="col-md-6"><label for="ano_conclusao" class="form-label">Ano de conclusão</label><input type="number" id="ano_conclusao" name="ano_conclusao" class="form-control" min="1950" max="<?=date('Y')?>" required value="<?=escapar((string)($cursoEditado['ano_conclusao']??''))?>"></div></div>
                <div class="d-flex gap-2"><button class="btn btn-success"><?=$cursoEditado?'Salvar alterações':'Adicionar curso'?></button><?php if($cursoEditado):?><a href="<?=url('candidato/cursos.php')?>" class="btn btn-outline-secondary">Cancelar</a><?php endif;?></div>
            </form>
        </div></div></div>
        <div class="col-lg-7"><h2 class="h5 mb-3">Cursos cadastrados</h2><?php if(!$cursos):?><div class="alert alert-light border">Nenhum curso cadastrado.</div><?php endif;?>
            <?php foreach($cursos as $curso):?><article class="card shadow-sm border-0 mb-3"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3"><div><h3 class="h5 mb-1"><?=escapar($curso['nome'])?></h3><p class="text-secondary mb-2"><?=escapar($curso['instituicao'])?></p><small class="text-secondary"><?=(int)$curso['carga_horaria']?> horas · Conclusão em <?=escapar((string)$curso['ano_conclusao'])?></small></div><div class="d-flex gap-2 align-items-start"><a href="<?=url('candidato/cursos.php?editar='.(int)$curso['id'])?>" class="btn btn-sm btn-outline-primary" aria-label="Editar curso"><i class="fa-solid fa-pen"></i></a><form action="<?=url('actions/excluir_curso.php')?>" method="post" onsubmit="return confirm('Excluir este curso?');"><input type="hidden" name="csrf_token" value="<?=escapar(tokenCsrf())?>"><input type="hidden" name="id" value="<?=(int)$curso['id']?>"><button class="btn btn-sm btn-outline-danger" aria-label="Excluir curso"><i class="fa-solid fa-trash"></i></button></form></div></div></div></article><?php endforeach;?>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

