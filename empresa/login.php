<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

if (empresaAutenticada()) {
    redirecionar('empresa/painel.php');
}

$tituloPagina = 'Acesso da empresa';
$mensagemErro = obterFlash('erro');
$mensagemSucesso = obterFlash('sucesso');
$cnpjInformado = obterFlash('cnpj', '');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 620px;">
        <div class="card-header bg-primary text-white p-4 text-center">
            <i class="fa-solid fa-building-lock fa-3x mb-3" aria-hidden="true"></i>
            <h1 class="h3 mb-1">Acesso da empresa</h1>
            <p class="mb-0 opacity-75">Entre com o CNPJ e a senha cadastrada.</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <?php if ($mensagemErro): ?><div class="alert alert-danger" role="alert"><?= escapar((string) $mensagemErro) ?></div><?php endif; ?>
            <?php if ($mensagemSucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string) $mensagemSucesso) ?></div><?php endif; ?>

            <form action="<?= url('actions/autenticar_empresa.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <div class="mb-3">
                    <label for="cnpj" class="form-label">CNPJ</label>
                    <input type="text" id="cnpj" name="cnpj" class="form-control form-control-lg" inputmode="numeric" maxlength="18" placeholder="00.000.000/0000-00" autocomplete="username" required value="<?= escapar((string) $cnpjInformado) ?>">
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control form-control-lg" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>Entrar</button>
            </form>
            <p class="text-center text-secondary mt-4 mb-0">Ainda não possui cadastro? <a href="<?= url('empresa/cadastro.php') ?>">Cadastre sua empresa</a></p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
