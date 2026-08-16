<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

$tituloPagina = 'Recuperar senha';
$mensagem = obterFlash('sucesso');
$erro = obterFlash('erro');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 620px;">
        <div class="card-header bg-primary text-white p-4 text-center">
            <i class="fa-solid fa-key fa-3x mb-3" aria-hidden="true"></i>
            <h1 class="h3 mb-1">Recuperar senha</h1>
            <p class="mb-0 opacity-75">Informe seu CPF para receber as instruções.</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <?php if ($mensagem): ?>
                <div class="alert alert-success" role="alert"><?= escapar((string) $mensagem) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-danger" role="alert"><?= escapar((string) $erro) ?></div>
            <?php endif; ?>

            <form action="<?= url('actions/solicitar_recuperacao.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <div class="mb-4">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" id="cpf" name="cpf" class="form-control form-control-lg"
                        inputmode="numeric" maxlength="14"
                        pattern="[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}"
                        placeholder="000.000.000-00" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Enviar link de recuperação</button>
            </form>
            <p class="text-center mt-4 mb-0"><a href="<?= url('candidato/login.php') ?>">Voltar ao login</a></p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

