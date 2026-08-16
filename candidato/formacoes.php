<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$candidatoId = obterCandidatoId($pdo);
$editarId = filter_var($_GET['editar'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$formacaoEditada = null;

if ($editarId) {
    $consulta = $pdo->prepare('SELECT * FROM formacoes WHERE id = ? AND candidato_id = ? LIMIT 1');
    $consulta->execute([$editarId, $candidatoId]);
    $formacaoEditada = $consulta->fetch() ?: null;
}

$consulta = $pdo->prepare('SELECT * FROM formacoes WHERE candidato_id = ? ORDER BY cursando DESC, data_conclusao DESC, data_inicio DESC');
$consulta->execute([$candidatoId]);
$formacoes = $consulta->fetchAll();

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

$sucesso = obterFlash('sucesso');
$erros = obterFlash('erros', []);
$tituloPagina = 'Formações';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Currículo digital</p><h1 class="h2 mb-1">Formações</h1><p class="text-secondary mb-0">Cadastre sua escolaridade e formação acadêmica.</p></div>
        <a href="<?= url('candidato/painel.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a>
    </div>
    <?php if ($sucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string) $sucesso) ?></div><?php endif; ?>
    <?php if ($erros): ?><div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($erros as $erro): ?><li><?= escapar((string) $erro) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><?= $formacaoEditada ? 'Editar formação' : 'Adicionar formação' ?></h2>
                    <form action="<?= url('actions/salvar_formacao.php') ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                        <input type="hidden" name="id" value="<?= (int) ($formacaoEditada['id'] ?? 0) ?>">
                        <div class="mb-3"><label for="nivel" class="form-label">Nível de escolaridade</label><select id="nivel" name="nivel" class="form-select" required><option value="">Selecione</option><?php foreach ($niveis as $valor => $rotulo): ?><option value="<?= escapar($valor) ?>" <?= ($formacaoEditada['nivel'] ?? '') === $valor ? 'selected' : '' ?>><?= escapar($rotulo) ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label for="instituicao" class="form-label">Instituição</label><input type="text" id="instituicao" name="instituicao" class="form-control" maxlength="180" required value="<?= escapar((string) ($formacaoEditada['instituicao'] ?? '')) ?>"></div>
                        <div class="mb-3"><label for="curso" class="form-label">Curso ou área de estudo</label><input type="text" id="curso" name="curso" class="form-control" maxlength="180" value="<?= escapar((string) ($formacaoEditada['curso'] ?? '')) ?>"><div class="form-text">Opcional para Ensino Fundamental e Médio.</div></div>
                        <div class="row g-3 mb-3"><div class="col-md-6"><label for="formacao_inicio" class="form-label">Data de início</label><input type="date" id="formacao_inicio" name="data_inicio" class="form-control" required value="<?= escapar((string) ($formacaoEditada['data_inicio'] ?? '')) ?>"></div><div class="col-md-6"><label for="formacao_fim" class="form-label">Data de conclusão</label><input type="date" id="formacao_fim" name="data_conclusao" class="form-control" data-end-date value="<?= escapar((string) ($formacaoEditada['data_conclusao'] ?? '')) ?>"></div></div>
                        <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="formacao_cursando" name="cursando" value="1" data-current-toggle data-target="#formacao_fim" <?= !empty($formacaoEditada['cursando']) ? 'checked' : '' ?>><label class="form-check-label" for="formacao_cursando">Estou cursando atualmente</label></div>
                        <div class="d-flex gap-2"><button type="submit" class="btn btn-success"><?= $formacaoEditada ? 'Salvar alterações' : 'Adicionar formação' ?></button><?php if ($formacaoEditada): ?><a href="<?= url('candidato/formacoes.php') ?>" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h2 class="h5 mb-3">Formações cadastradas</h2>
            <?php if (!$formacoes): ?><div class="alert alert-light border">Nenhuma formação cadastrada.</div><?php endif; ?>
            <?php foreach ($formacoes as $formacao): ?>
                <article class="card shadow-sm border-0 mb-3"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3"><div><h3 class="h5 mb-1"><?= escapar($niveis[$formacao['nivel']] ?? $formacao['nivel']) ?></h3><p class="mb-1 fw-semibold"><?= escapar($formacao['instituicao']) ?></p><?php if ($formacao['curso']): ?><p class="text-secondary mb-2"><?= escapar($formacao['curso']) ?></p><?php endif; ?><small class="text-secondary"><?= date('d/m/Y', strtotime($formacao['data_inicio'])) ?> — <?= $formacao['cursando'] ? 'Cursando' : date('d/m/Y', strtotime($formacao['data_conclusao'])) ?></small></div><div class="d-flex gap-2 align-items-start"><a href="<?= url('candidato/formacoes.php?editar=' . (int) $formacao['id']) ?>" class="btn btn-sm btn-outline-primary" aria-label="Editar formação"><i class="fa-solid fa-pen"></i></a><form action="<?= url('actions/excluir_formacao.php') ?>" method="post" onsubmit="return confirm('Excluir esta formação?');"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="id" value="<?= (int) $formacao['id'] ?>"><button class="btn btn-sm btn-outline-danger" aria-label="Excluir formação"><i class="fa-solid fa-trash"></i></button></form></div></div></div></article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

