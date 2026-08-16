<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

if (administradorAutenticado()) {
    redirecionar('admin/dashboard.php');
}

$tituloPagina = 'Acesso da Prefeitura';
$mensagemErro = obterFlash('erro');
$mensagemSucesso = obterFlash('sucesso');
$emailInformado = obterFlash('email', '');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 620px;">
        <div class="card-header bg-primary text-white p-4 text-center">
            <i class="fa-solid fa-landmark fa-3x mb-3" aria-hidden="true"></i>
            <h1 class="h3 mb-1">Acesso da Prefeitura</h1>
            <p class="mb-0 opacity-75">Área restrita à gestão municipal.</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <?php if ($mensagemErro): ?><div class="alert alert-danger" role="alert"><?= escapar((string)$mensagemErro) ?></div><?php endif; ?>
            <?php if ($mensagemSucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string)$mensagemSucesso) ?></div><?php endif; ?>

            <form action="<?= url('actions/autenticar_admin.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <div class="mb-3"><label for="email" class="form-label">E-mail institucional</label><input type="email" id="email" name="email" class="form-control form-control-lg" maxlength="190" autocomplete="username" required value="<?= escapar((string)$emailInformado) ?>"></div>
                <div class="mb-4"><label for="senha" class="form-label">Senha</label><input type="password" id="senha" name="senha" class="form-control form-control-lg" autocomplete="current-password" required></div>
                <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>Entrar no painel</button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
