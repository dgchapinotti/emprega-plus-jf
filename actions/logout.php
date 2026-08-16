<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    redirecionar();
}

$perfilAnterior = $_SESSION['perfil'] ?? null;
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();
iniciarSessao();
definirFlash('sucesso', 'Você saiu da sua conta com segurança.');
$destinos = [
    'empresa' => 'empresa/login.php',
    'administrador' => 'admin/login.php',
    'candidato' => 'candidato/login.php',
];
redirecionar($destinos[$perfilAnterior] ?? '');
