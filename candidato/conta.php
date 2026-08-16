<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirCandidato();

$consulta = $pdo->prepare(
    'SELECT u.nome, u.email, c.cpf
     FROM usuarios u
     INNER JOIN candidatos c ON c.usuario_id = u.id
     WHERE u.id = ? AND u.perfil = \'candidato\'
     LIMIT 1'
);
$consulta->execute([(int) $_SESSION['usuario_id']]);
$conta = $consulta->fetch();

if (!$conta) {
    $_SESSION = [];
    session_destroy();
    redirecionar('candidato/login.php');
}

$cpfFormatado = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $conta['cpf']);
$tituloPagina = 'Minha conta';
$sucesso = obterFlash('sucesso');
$erroEmail = obterFlash('erro_email');
$erroSenha = obterFlash('erro_senha');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="mb-4">
        <p class="text-primary fw-semibold mb-1">Área do candidato</p>
        <h1 class="h2 mb-1">Minha conta</h1>
        <p class="text-secondary mb-0">Gerencie seus dados de acesso com segurança.</p>
    </div>

    <?php if ($sucesso): ?>
        <div class="alert alert-success" role="alert"><?= escapar((string) $sucesso) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Identificação</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nome completo</label>
                    <input class="form-control" value="<?= escapar($conta['nome']) ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CPF</label>
                    <input class="form-control" value="<?= escapar((string) $cpfFormatado) ?>" disabled>
                </div>
            </div>
            <p class="form-text mb-0 mt-3">Nome e CPF fazem parte da identificação cadastral e não podem ser alterados nesta tela.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><i class="fa-solid fa-envelope text-primary me-2" aria-hidden="true"></i>Alterar e-mail</h2>

                    <?php if ($erroEmail): ?>
                        <div class="alert alert-danger" role="alert"><?= escapar((string) $erroEmail) ?></div>
                    <?php endif; ?>

                    <form action="<?= url('actions/atualizar_email.php') ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                        <div class="mb-3">
                            <label for="novo_email" class="form-label">Novo e-mail</label>
                            <input type="email" id="novo_email" name="novo_email" class="form-control"
                                maxlength="190" autocomplete="email" required value="<?= escapar($conta['email']) ?>">
                        </div>
                        <div class="mb-4">
                            <label for="senha_email" class="form-label">Senha atual</label>
                            <input type="password" id="senha_email" name="senha_atual" class="form-control"
                                autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar novo e-mail</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><i class="fa-solid fa-lock text-primary me-2" aria-hidden="true"></i>Alterar senha</h2>

                    <?php if ($erroSenha): ?>
                        <div class="alert alert-danger" role="alert"><?= escapar((string) $erroSenha) ?></div>
                    <?php endif; ?>

                    <form action="<?= url('actions/alterar_senha.php') ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" class="form-control"
                                autocomplete="current-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="nova_senha" class="form-label">Nova senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" class="form-control"
                                minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">Confirme a nova senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control"
                                minlength="8" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Alterar senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

