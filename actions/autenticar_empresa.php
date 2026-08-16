<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('empresa/login.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('empresa/login.php');
}

$cnpjRecebido = (string) ($_POST['cnpj'] ?? '');
$cnpj = somenteDigitos($cnpjRecebido);
$senha = (string) ($_POST['senha'] ?? '');

if (!cnpjValido($cnpj) || $senha === '') {
    definirFlash('erro', 'CNPJ ou senha inválidos.');
    definirFlash('cnpj', $cnpjRecebido);
    redirecionar('empresa/login.php');
}

$consulta = $pdo->prepare(
    "SELECT u.id AS usuario_id, u.nome, u.senha_hash, u.status,
            e.id AS empresa_id, e.aprovada_em
     FROM usuarios u
     INNER JOIN empresas e ON e.usuario_id = u.id
     WHERE e.cnpj = ? AND u.perfil = 'empresa'
     LIMIT 1"
);
$consulta->execute([$cnpj]);
$empresa = $consulta->fetch();

if (!$empresa || !password_verify($senha, $empresa['senha_hash'])) {
    definirFlash('erro', 'CNPJ ou senha inválidos.');
    definirFlash('cnpj', $cnpjRecebido);
    redirecionar('empresa/login.php');
}

if ($empresa['status'] === 'pendente' || empty($empresa['aprovada_em'])) {
    definirFlash('erro', 'O cadastro da empresa ainda está aguardando aprovação da Prefeitura.');
    definirFlash('cnpj', $cnpjRecebido);
    redirecionar('empresa/login.php');
}

if ($empresa['status'] !== 'ativo') {
    definirFlash('erro', 'O acesso desta empresa está indisponível. Procure a Prefeitura.');
    definirFlash('cnpj', $cnpjRecebido);
    redirecionar('empresa/login.php');
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $empresa['usuario_id'];
$_SESSION['empresa_id'] = (int) $empresa['empresa_id'];
$_SESSION['perfil'] = 'empresa';
$_SESSION['nome'] = $empresa['nome'];
unset($_SESSION['csrf_token']);

$atualizar = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
$atualizar->execute([(int) $empresa['usuario_id']]);

definirFlash('sucesso', 'Login realizado com sucesso.');
redirecionar('empresa/painel.php');
