<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirCandidato();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/conta.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro_senha', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('candidato/conta.php');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$senhaAtual = (string) ($_POST['senha_atual'] ?? '');
$novaSenha = (string) ($_POST['nova_senha'] ?? '');
$confirmacao = (string) ($_POST['confirmar_senha'] ?? '');

if (strlen($novaSenha) < 8) {
    definirFlash('erro_senha', 'A nova senha deve ter pelo menos 8 caracteres.');
    redirecionar('candidato/conta.php');
}

if ($novaSenha !== $confirmacao) {
    definirFlash('erro_senha', 'A confirmação da nova senha não corresponde.');
    redirecionar('candidato/conta.php');
}

$consulta = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = ? AND perfil = \'candidato\' LIMIT 1');
$consulta->execute([$usuarioId]);
$usuario = $consulta->fetch();

if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
    definirFlash('erro_senha', 'A senha atual está incorreta.');
    redirecionar('candidato/conta.php');
}

if (password_verify($novaSenha, $usuario['senha_hash'])) {
    definirFlash('erro_senha', 'A nova senha deve ser diferente da senha atual.');
    redirecionar('candidato/conta.php');
}

$atualizar = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
$atualizar->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $usuarioId]);
session_regenerate_id(true);

definirFlash('sucesso', 'Senha alterada com sucesso.');
redirecionar('candidato/conta.php');
