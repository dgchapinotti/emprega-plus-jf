<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

if (candidatoAutenticado()) {
    redirecionar('candidato/painel.php');
}

$tituloPagina = 'Acesso do candidato';
$mensagemErro = obterFlash('erro');
$mensagemSucesso = obterFlash('sucesso');
$cpfInformado = obterFlash('cpf', '');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 620px;">
        <div class="card-header bg-primary text-white p-4 text-center">
            <i class="fa-solid fa-user-lock fa-3x mb-3" aria-hidden="true"></i>
            <h1 class="h3 mb-1">Acesso do candidato</h1>
            <p class="mb-0 opacity-75">Entre com seu CPF e sua senha.</p>
        </div>
        <div class="card-body p-4 p-md-5">

            <?php if ($mensagemErro): ?>
                <div class="alert alert-danger" role="alert">
                    <?= escapar((string) $mensagemErro) ?>
                </div>
            <?php endif; ?>

            <?php if ($mensagemSucesso): ?>
                <div class="alert alert-success" role="alert">
                    <?= escapar((string) $mensagemSucesso) ?>
                </div>
            <?php endif; ?>

            <form action="<?= url('actions/autenticar_candidato.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">

                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" id="cpf" name="cpf" class="form-control form-control-lg"
                        inputmode="numeric" maxlength="14"
                        pattern="[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}"
                        placeholder="000.000.000-00" autocomplete="username" required
                        value="<?= escapar((string) $cpfInformado) ?>">
                </div>

                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control form-control-lg"
                        autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>Entrar
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="<?= url('candidato/esqueci-senha.php') ?>">Esqueci minha senha</a>
            </p>

            <p class="text-center text-secondary mt-4 mb-0">
                Ainda não possui cadastro?
                <a href="<?= url('candidato/cadastro.php') ?>">Crie sua conta</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
