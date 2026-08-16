<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
require_once __DIR__ . '/../includes/email.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/esqueci-senha.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('candidato/esqueci-senha.php');
}

$cpf = somenteDigitos((string) ($_POST['cpf'] ?? ''));
$mensagemGenerica = 'Se o CPF estiver cadastrado, enviaremos um link para o e-mail associado à conta.';

if (!cpfValido($cpf)) {
    definirFlash('sucesso', $mensagemGenerica);
    redirecionar('candidato/esqueci-senha.php');
}

$consulta = $pdo->prepare(
    "SELECT u.id, u.nome, u.email
     FROM usuarios u
     INNER JOIN candidatos c ON c.usuario_id = u.id
     WHERE c.cpf = ? AND u.perfil = 'candidato' AND u.status = 'ativo'
     LIMIT 1"
);
$consulta->execute([$cpf]);
$usuario = $consulta->fetch();

if ($usuario) {
    $limite = $pdo->prepare(
        "SELECT COUNT(*) FROM recuperacoes_senha
         WHERE usuario_id = ? AND criado_em >= (NOW() - INTERVAL 5 MINUTE)"
    );
    $limite->execute([(int) $usuario['id']]);

    if ((int) $limite->fetchColumn() === 0) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

        try {
            $pdo->beginTransaction();
            $invalidar = $pdo->prepare(
                'UPDATE recuperacoes_senha SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL'
            );
            $invalidar->execute([(int) $usuario['id']]);

            $inserir = $pdo->prepare(
                'INSERT INTO recuperacoes_senha (usuario_id, token_hash, expira_em, solicitado_ip)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), ?)'
            );
            $inserir->execute([(int) $usuario['id'], $tokenHash, $ip ?: null]);
            $recuperacaoId = (int) $pdo->lastInsertId();

            $link = URL_SISTEMA . '/candidato/redefinir-senha.php?token=' . rawurlencode($token);
            enviarEmailRecuperacao($usuario['email'], $usuario['nome'], $link);
            $pdo->commit();
        } catch (Throwable $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erro na recuperação de senha: ' . $erro->getMessage());
        }
    }
}

definirFlash('sucesso', $mensagemGenerica);
redirecionar('candidato/esqueci-senha.php');

