<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/login.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('candidato/login.php');
}

$cpfRecebido = (string) ($_POST['cpf'] ?? '');
$cpf = somenteDigitos($cpfRecebido);
$senha = (string) ($_POST['senha'] ?? '');

if (!cpfValido($cpf) || $senha === '') {
    definirFlash('erro', 'CPF ou senha inválidos.');
    definirFlash('cpf', $cpfRecebido);
    redirecionar('candidato/login.php');
}

$consulta = $pdo->prepare(
    "SELECT
        u.id AS usuario_id,
        u.nome,
        u.senha_hash,
        u.status,
        c.id AS candidato_id
     FROM usuarios u
     INNER JOIN candidatos c ON c.usuario_id = u.id
     WHERE c.cpf = ? AND u.perfil = 'candidato'
     LIMIT 1"
);
$consulta->execute([$cpf]);
$usuario = $consulta->fetch();

$credenciaisValidas = $usuario
    && $usuario['status'] === 'ativo'
    && password_verify($senha, $usuario['senha_hash']);

if (!$credenciaisValidas) {
    definirFlash('erro', 'CPF ou senha inválidos.');
    definirFlash('cpf', $cpfRecebido);
    redirecionar('candidato/login.php');
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $usuario['usuario_id'];
$_SESSION['candidato_id'] = (int) $usuario['candidato_id'];
$_SESSION['perfil'] = 'candidato';
$_SESSION['nome'] = $usuario['nome'];
unset($_SESSION['csrf_token']);

$atualizarAcesso = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
$atualizarAcesso->execute([(int) $usuario['usuario_id']]);

definirFlash('sucesso', 'Login realizado com sucesso.');
redirecionar('candidato/painel.php');

