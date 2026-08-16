<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('empresa/cadastro.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erros', ['Sua sessão expirou. Atualize a página e tente novamente.']);
    redirecionar('empresa/cadastro.php');
}

$dados = [
    'cnpj' => somenteDigitos((string) ($_POST['cnpj'] ?? '')),
    'razao_social' => trim((string) ($_POST['razao_social'] ?? '')),
    'nome_fantasia' => trim((string) ($_POST['nome_fantasia'] ?? '')),
    'responsavel_nome' => trim((string) ($_POST['responsavel_nome'] ?? '')),
    'telefone' => trim((string) ($_POST['telefone'] ?? '')),
    'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
    'cep' => somenteDigitos((string) ($_POST['cep'] ?? '')),
    'logradouro' => trim((string) ($_POST['logradouro'] ?? '')),
    'numero' => trim((string) ($_POST['numero'] ?? '')),
    'complemento' => trim((string) ($_POST['complemento'] ?? '')),
    'bairro' => trim((string) ($_POST['bairro'] ?? '')),
    'cidade_id' => filter_var($_POST['cidade_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
];

$senha = (string) ($_POST['senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');
$erros = [];

if (!cnpjValido($dados['cnpj'])) $erros[] = 'Informe um CNPJ válido.';
if (mb_strlen($dados['razao_social']) < 3) $erros[] = 'Informe a razão social.';
if (mb_strlen($dados['responsavel_nome']) < 3) $erros[] = 'Informe o responsável pela empresa.';
if (!in_array(strlen(somenteDigitos($dados['telefone'])), [10, 11], true)) $erros[] = 'Informe um telefone válido com DDD.';
if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
if (strlen($dados['cep']) !== 8 || $dados['logradouro'] === '' || $dados['numero'] === '' || $dados['bairro'] === '') $erros[] = 'Preencha o endereço completo.';
if ($dados['cidade_id'] <= 0) $erros[] = 'Selecione a cidade.';
if (strlen($senha) < 8) $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
if ($senha !== $confirmarSenha) $erros[] = 'A confirmação da senha não corresponde.';
if (($_POST['consentimento'] ?? null) !== '1') $erros[] = 'É necessário aceitar a declaração de responsabilidade.';

if ($erros) {
    definirFlash('erros', $erros);
    definirFlash('dados', $dados);
    redirecionar('empresa/cadastro.php');
}

try {
    $pdo->beginTransaction();

    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $consulta->execute([$dados['email']]);
    if ($consulta->fetch()) throw new DomainException('Já existe uma conta cadastrada com este e-mail.');

    $consulta = $pdo->prepare('SELECT id FROM empresas WHERE cnpj = ? LIMIT 1');
    $consulta->execute([$dados['cnpj']]);
    if ($consulta->fetch()) throw new DomainException('Já existe uma empresa cadastrada com este CNPJ.');

    $consulta = $pdo->prepare("SELECT id FROM cidades WHERE id = ? AND uf = 'MG' AND codigo_ibge IS NOT NULL LIMIT 1");
    $consulta->execute([$dados['cidade_id']]);
    $cidadeId = $consulta->fetchColumn();
    if (!$cidadeId) throw new DomainException('A cidade selecionada não pertence à região atendida.');

    $inserirUsuario = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, status) VALUES (?, ?, ?, 'empresa', 'pendente')");
    $inserirUsuario->execute([$dados['nome_fantasia'] ?: $dados['razao_social'], $dados['email'], password_hash($senha, PASSWORD_DEFAULT)]);
    $usuarioId = (int) $pdo->lastInsertId();

    $inserirEmpresa = $pdo->prepare('INSERT INTO empresas (usuario_id, cidade_id, cnpj, razao_social, nome_fantasia, telefone, cep, logradouro, numero, complemento, bairro, responsavel_nome) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $inserirEmpresa->execute([$usuarioId, (int) $cidadeId, $dados['cnpj'], $dados['razao_social'], $dados['nome_fantasia'] ?: null, $dados['telefone'], $dados['cep'], $dados['logradouro'], $dados['numero'], $dados['complemento'] ?: null, $dados['bairro'], $dados['responsavel_nome']]);

    $pdo->commit();
    unset($_SESSION['csrf_token']);
    definirFlash('sucesso', 'Cadastro enviado com sucesso. Aguarde a aprovação da Prefeitura para acessar o sistema.');
    redirecionar('empresa/cadastro.php');
} catch (DomainException $erro) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    definirFlash('erros', [$erro->getMessage()]);
    definirFlash('dados', $dados);
    redirecionar('empresa/cadastro.php');
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao cadastrar empresa: ' . $erro->getMessage());
    definirFlash('erros', ['Não foi possível concluir o cadastro. Tente novamente.']);
    definirFlash('dados', $dados);
    redirecionar('empresa/cadastro.php');
}
