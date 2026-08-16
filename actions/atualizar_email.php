<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirCandidato();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/conta.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro_email', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('candidato/conta.php');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$novoEmail = strtolower(trim((string) ($_POST['novo_email'] ?? '')));
$senhaAtual = (string) ($_POST['senha_atual'] ?? '');

if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
    definirFlash('erro_email', 'Informe um e-mail válido.');
    redirecionar('candidato/conta.php');
}

$consulta = $pdo->prepare('SELECT email, senha_hash FROM usuarios WHERE id = ? AND perfil = \'candidato\' LIMIT 1');
$consulta->execute([$usuarioId]);
$usuario = $consulta->fetch();

if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
    definirFlash('erro_email', 'A senha atual está incorreta.');
    redirecionar('candidato/conta.php');
}

if ($novoEmail === strtolower($usuario['email'])) {
    definirFlash('erro_email', 'O novo e-mail é igual ao endereço atual.');
    redirecionar('candidato/conta.php');
}

$duplicado = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
$duplicado->execute([$novoEmail, $usuarioId]);

if ($duplicado->fetch()) {
    definirFlash('erro_email', 'Este e-mail já está vinculado a outra conta.');
    redirecionar('candidato/conta.php');
}

$atualizar = $pdo->prepare('UPDATE usuarios SET email = ? WHERE id = ?');
$atualizar->execute([$novoEmail, $usuarioId]);
session_regenerate_id(true);

definirFlash('sucesso', 'E-mail alterado com sucesso.');
redirecionar('candidato/conta.php');

