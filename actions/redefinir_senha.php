<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/esqueci-senha.php');
}

$token = (string) ($_POST['token'] ?? '');
$senha = (string) ($_POST['senha'] ?? '');
$confirmacao = (string) ($_POST['confirmar_senha'] ?? '');
$erros = [];

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    $erros[] = 'Sua sessão expirou. Atualize a página e tente novamente.';
}
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $erros[] = 'O link de recuperação é inválido.';
}
if (strlen($senha) < 8) {
    $erros[] = 'A nova senha deve ter pelo menos 8 caracteres.';
}
if ($senha !== $confirmacao) {
    $erros[] = 'A confirmação da senha não corresponde.';
}

if ($erros) {
    definirFlash('erros', $erros);
    header('Location: ' . url('candidato/redefinir-senha.php') . '?token=' . rawurlencode($token));
    exit;
}

try {
    $pdo->beginTransaction();
    $consulta = $pdo->prepare(
        'SELECT id, usuario_id FROM recuperacoes_senha
         WHERE token_hash = ? AND utilizado_em IS NULL AND expira_em > NOW()
         LIMIT 1 FOR UPDATE'
    );
    $consulta->execute([hash('sha256', $token)]);
    $recuperacao = $consulta->fetch();

    if (!$recuperacao) {
        throw new DomainException('Este link é inválido, já foi utilizado ou expirou.');
    }

    $atualizar = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
    $atualizar->execute([password_hash($senha, PASSWORD_DEFAULT), (int) $recuperacao['usuario_id']]);

    $invalidar = $pdo->prepare(
        'UPDATE recuperacoes_senha SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL'
    );
    $invalidar->execute([(int) $recuperacao['usuario_id']]);
    $pdo->commit();

    definirFlash('sucesso', 'Senha alterada com sucesso. Entre com sua nova senha.');
    redirecionar('candidato/login.php');
} catch (DomainException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    definirFlash('erros', [$erro->getMessage()]);
    header('Location: ' . url('candidato/redefinir-senha.php') . '?token=' . rawurlencode($token));
    exit;
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao redefinir senha: ' . $erro->getMessage());
    definirFlash('erros', ['Não foi possível alterar a senha. Tente novamente.']);
    header('Location: ' . url('candidato/redefinir-senha.php') . '?token=' . rawurlencode($token));
    exit;
}
