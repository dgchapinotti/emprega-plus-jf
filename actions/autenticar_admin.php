<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('admin/login.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('admin/login.php');
}

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$senha = (string)($_POST['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    definirFlash('erro', 'E-mail ou senha inválidos.');
    definirFlash('email', $email);
    redirecionar('admin/login.php');
}

$consulta = $pdo->prepare(
    "SELECT u.id,u.nome,u.email,u.senha_hash,u.status,a.nivel
     FROM usuarios u
     INNER JOIN administradores a ON a.usuario_id=u.id
     WHERE u.email=? AND u.perfil='administrador'
     LIMIT 1"
);
$consulta->execute([$email]);
$administrador = $consulta->fetch();

if (!$administrador || $administrador['status'] !== 'ativo' || !password_verify($senha, $administrador['senha_hash'])) {
    definirFlash('erro', 'E-mail ou senha inválidos.');
    definirFlash('email', $email);
    redirecionar('admin/login.php');
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int)$administrador['id'];
$_SESSION['perfil'] = 'administrador';
$_SESSION['nome'] = $administrador['nome'];
$_SESSION['admin_nivel'] = $administrador['nivel'];
unset($_SESSION['csrf_token']);

$atualizar = $pdo->prepare('UPDATE usuarios SET ultimo_acesso=NOW() WHERE id=?');
$atualizar->execute([(int)$administrador['id']]);

definirFlash('sucesso', 'Acesso administrativo realizado com sucesso.');
redirecionar('admin/dashboard.php');
