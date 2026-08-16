<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('candidato/cadastro.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erros', ['Sua sessão expirou. Atualize a página e tente novamente.']);
    redirecionar('candidato/cadastro.php');
}

$dados = [
    'nome_completo' => trim((string) ($_POST['nome_completo'] ?? '')),
    'cpf' => somenteDigitos((string) ($_POST['cpf'] ?? '')),
    'data_nascimento' => trim((string) ($_POST['data_nascimento'] ?? '')),
    'telefone' => trim((string) ($_POST['telefone'] ?? '')),
    'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
    'cidade_id' => filter_var($_POST['cidade_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
];

$senha = (string) ($_POST['senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
$erros = [];

if (mb_strlen($dados['nome_completo']) < 3) {
    $erros[] = 'Informe seu nome completo.';
}

if (!cpfValido($dados['cpf'])) {
    $erros[] = 'Informe um CPF válido.';
}

$dataNascimento = DateTimeImmutable::createFromFormat('!Y-m-d', $dados['data_nascimento']);
if (!$dataNascimento || $dataNascimento->format('Y-m-d') !== $dados['data_nascimento'] || $dataNascimento > new DateTimeImmutable('today')) {
    $erros[] = 'Informe uma data de nascimento válida.';
}

$telefoneDigitos = somenteDigitos($dados['telefone']);
if (strlen($telefoneDigitos) !== 11) {
    $erros[] = 'Informe um celular com DDD e nove dígitos.';
}

if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Informe um e-mail válido.';
}

if ($dados['cidade_id'] <= 0) {
    $erros[] = 'Selecione sua cidade.';
}

if (strlen($senha) < 8) {
    $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
}

if ($senha !== $confirmarSenha) {
    $erros[] = 'A confirmação da senha não corresponde.';
}

if (($_POST['consentimento'] ?? null) !== '1') {
    $erros[] = 'É necessário autorizar o tratamento dos dados para criar a conta.';
}

if ($erros) {
    definirFlash('erros', $erros);
    definirFlash('dados', $dados);
    redirecionar('candidato/cadastro.php');
}

try {
    $pdo->beginTransaction();

    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $consulta->execute([$dados['email']]);
    if ($consulta->fetch()) {
        throw new DomainException('Já existe uma conta cadastrada com este e-mail.');
    }

    $consulta = $pdo->prepare('SELECT id FROM candidatos WHERE cpf = ? LIMIT 1');
    $consulta->execute([$dados['cpf']]);
    if ($consulta->fetch()) {
        throw new DomainException('Já existe uma conta cadastrada com este CPF.');
    }

    $consulta = $pdo->prepare(
        "SELECT id FROM cidades
         WHERE id = ? AND uf = 'MG' AND codigo_ibge IS NOT NULL
         LIMIT 1"
    );
    $consulta->execute([$dados['cidade_id']]);
    $cidadeId = $consulta->fetchColumn();

    if (!$cidadeId) {
        throw new DomainException('A cidade selecionada não pertence à região atendida.');
    }

    $inserirUsuario = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha_hash, perfil, status)
         VALUES (?, ?, ?, 'candidato', 'ativo')"
    );
    $inserirUsuario->execute([
        $dados['nome_completo'],
        $dados['email'],
        password_hash($senha, PASSWORD_DEFAULT),
    ]);
    $usuarioId = (int) $pdo->lastInsertId();

    $inserirCandidato = $pdo->prepare(
        'INSERT INTO candidatos
            (usuario_id, cidade_id, cpf, nome_completo, data_nascimento, telefone, consentimento_dados_em)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $inserirCandidato->execute([
        $usuarioId,
        (int) $cidadeId,
        $dados['cpf'],
        $dados['nome_completo'],
        $dados['data_nascimento'],
        $dados['telefone'],
    ]);
    $candidatoId = (int) $pdo->lastInsertId();

    $pdo->commit();

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuarioId;
    $_SESSION['candidato_id'] = $candidatoId;
    $_SESSION['perfil'] = 'candidato';
    $_SESSION['nome'] = $dados['nome_completo'];
    unset($_SESSION['csrf_token']);

    definirFlash('sucesso', 'Sua conta foi criada. Agora vamos montar seu currículo digital.');
    redirecionar('candidato/painel.php');
} catch (DomainException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    definirFlash('erros', [$erro->getMessage()]);
    definirFlash('dados', $dados);
    redirecionar('candidato/cadastro.php');
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erro ao cadastrar candidato: ' . $erro->getMessage());
    definirFlash('erros', ['Não foi possível concluir o cadastro. Tente novamente.']);
    definirFlash('dados', $dados);
    redirecionar('candidato/cadastro.php');
}
