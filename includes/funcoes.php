<?php

declare(strict_types=1);

require_once __DIR__ . '/configuracao.php';

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

function tokenCsrf(): string
{
    iniciarSessao();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $token): bool
{
    iniciarSessao();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function definirFlash(string $chave, mixed $valor): void
{
    iniciarSessao();
    $_SESSION['flash'][$chave] = $valor;
}

function obterFlash(string $chave, mixed $padrao = null): mixed
{
    iniciarSessao();
    $valor = $_SESSION['flash'][$chave] ?? $padrao;
    unset($_SESSION['flash'][$chave]);

    return $valor;
}

function somenteDigitos(string $valor): string
{
    return preg_replace('/\D+/', '', $valor) ?? '';
}

function cpfValido(string $cpf): bool
{
    $cpf = somenteDigitos($cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    for ($digito = 9; $digito < 11; $digito++) {
        $soma = 0;

        for ($indice = 0; $indice < $digito; $indice++) {
            $soma += (int) $cpf[$indice] * (($digito + 1) - $indice);
        }

        $verificador = (10 * $soma) % 11;
        $verificador = $verificador === 10 ? 0 : $verificador;

        if ((int) $cpf[$digito] !== $verificador) {
            return false;
        }
    }

    return true;
}

function cnpjValido(string $cnpj): bool
{
    $cnpj = somenteDigitos($cnpj);

    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    foreach ([12, 13] as $tamanho) {
        $soma = 0;
        $peso = $tamanho - 7;

        for ($indice = 0; $indice < $tamanho; $indice++) {
            $soma += (int) $cnpj[$indice] * $peso;
            $peso--;
            if ($peso < 2) {
                $peso = 9;
            }
        }

        $resto = $soma % 11;
        $verificador = $resto < 2 ? 0 : 11 - $resto;

        if ((int) $cnpj[$tamanho] !== $verificador) {
            return false;
        }
    }

    return true;
}

function candidatoAutenticado(): bool
{
    iniciarSessao();

    return isset($_SESSION['usuario_id'])
        && ($_SESSION['perfil'] ?? null) === 'candidato';
}

function empresaAutenticada(): bool
{
    iniciarSessao();

    return isset($_SESSION['usuario_id'])
        && ($_SESSION['perfil'] ?? null) === 'empresa';
}

function administradorAutenticado(): bool
{
    iniciarSessao();

    return isset($_SESSION['usuario_id'])
        && ($_SESSION['perfil'] ?? null) === 'administrador';
}

function exigirAdministrador(): void
{
    if (!administradorAutenticado()) {
        definirFlash('erro', 'Entre com uma conta administrativa para acessar a Prefeitura.');
        redirecionar('admin/login.php');
    }
}

function administradorMaster(): bool
{
    return administradorAutenticado()
        && ($_SESSION['admin_nivel'] ?? null) === 'master';
}

function exigirAdministradorMaster(): void
{
    exigirAdministrador();

    if (!administradorMaster()) {
        definirFlash('erro', 'Esta operação é exclusiva do administrador master.');
        redirecionar('admin/dashboard.php');
    }
}

function exigirEmpresa(): void
{
    if (!empresaAutenticada()) {
        definirFlash('erro', 'Entre com a conta da empresa para acessar o painel.');
        redirecionar('empresa/login.php');
    }
}

function obterEmpresaId(PDO $pdo): int
{
    exigirEmpresa();

    if (!empty($_SESSION['empresa_id'])) {
        return (int) $_SESSION['empresa_id'];
    }

    $consulta = $pdo->prepare('SELECT id FROM empresas WHERE usuario_id = ? LIMIT 1');
    $consulta->execute([(int) $_SESSION['usuario_id']]);
    $empresaId = (int) $consulta->fetchColumn();

    if ($empresaId <= 0) {
        $_SESSION = [];
        session_destroy();
        redirecionar('empresa/login.php');
    }

    $_SESSION['empresa_id'] = $empresaId;
    return $empresaId;
}

function usuarioAutenticado(): bool
{
    iniciarSessao();

    return isset($_SESSION['usuario_id'], $_SESSION['perfil']);
}

function exigirCandidato(): void
{
    if (!candidatoAutenticado()) {
        definirFlash('erro', 'Entre com sua conta para acessar o painel.');
        redirecionar('candidato/login.php');
    }
}

function obterCandidatoId(PDO $pdo): int
{
    exigirCandidato();

    if (!empty($_SESSION['candidato_id'])) {
        return (int) $_SESSION['candidato_id'];
    }

    $consulta = $pdo->prepare('SELECT id FROM candidatos WHERE usuario_id = ? LIMIT 1');
    $consulta->execute([(int) $_SESSION['usuario_id']]);
    $candidatoId = (int) $consulta->fetchColumn();

    if ($candidatoId <= 0) {
        $_SESSION = [];
        session_destroy();
        redirecionar('candidato/login.php');
    }

    $_SESSION['candidato_id'] = $candidatoId;
    return $candidatoId;
}
