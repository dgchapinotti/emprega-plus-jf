<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$token = (string) ($_GET['token'] ?? '');
$tokenValido = false;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $consulta = $pdo->prepare(
        'SELECT id FROM recuperacoes_senha
         WHERE token_hash = ? AND utilizado_em IS NULL AND expira_em > NOW()
         LIMIT 1'
    );
    $consulta->execute([hash('sha256', $token)]);
    $tokenValido = (bool) $consulta->fetch();
}

$tituloPagina = 'Definir nova senha';
$erros = obterFlash('erros', []);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 620px;">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3">Definir nova senha</h1>

            <?php if (!$tokenValido): ?>
                <div class="alert alert-warning mt-4" role="alert">Este link é inválido, já foi utilizado ou expirou.</div>
                <a href="<?= url('candidato/esqueci-senha.php') ?>" class="btn btn-primary">Solicitar novo link</a>
            <?php else: ?>
                <?php if ($erros): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($erros as $erro): ?><li><?= escapar((string) $erro) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= url('actions/redefinir_senha.php') ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                    <input type="hidden" name="token" value="<?= escapar($token) ?>">
                    <div class="mb-3">
                        <label for="senha" class="form-label">Nova senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirmar_senha" class="form-label">Confirme a nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100">Salvar nova senha</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

