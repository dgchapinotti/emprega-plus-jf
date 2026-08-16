<?php

declare(strict_types=1);

require_once __DIR__ . '/configuracao.php';

$arquivoCredenciais = __DIR__ . '/credenciais.php';

if (!is_file($arquivoCredenciais)) {
    throw new RuntimeException('Arquivo privado de configuração do banco não encontrado.');
}

$ambientes = require $arquivoCredenciais;
$banco = $ambientes[AMBIENTE] ?? null;

if (!is_array($banco)) {
    throw new RuntimeException('Configuração do ambiente do banco não encontrada.');
}

$camposObrigatorios = ['host', 'porta', 'banco', 'usuario', 'senha'];

foreach ($camposObrigatorios as $campo) {
    if (!array_key_exists($campo, $banco)) {
        throw new RuntimeException('Configuração do banco incompleta.');
    }
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $banco['host'],
    $banco['porta'],
    $banco['banco']
);

try {
    $pdo = new PDO(
        $dsn,
        $banco['usuario'],
        $banco['senha'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $erro) {
    error_log('Falha na conexão com o banco: ' . $erro->getMessage());
    throw new RuntimeException('Não foi possível conectar ao banco de dados.');
}

