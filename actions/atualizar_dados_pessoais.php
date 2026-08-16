<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$candidatoId = obterCandidatoId($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar('candidato/dados-pessoais.php');

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erros', ['Sua sessão expirou. Atualize a página e tente novamente.']);
    redirecionar('candidato/dados-pessoais.php');
}

$telefone = somenteDigitos((string) ($_POST['telefone'] ?? ''));
$cep = somenteDigitos((string) ($_POST['cep'] ?? ''));
$logradouro = trim((string) ($_POST['logradouro'] ?? ''));
$numero = trim((string) ($_POST['numero'] ?? ''));
$complemento = trim((string) ($_POST['complemento'] ?? ''));
$bairro = trim((string) ($_POST['bairro'] ?? ''));
$cidadeId = filter_var($_POST['cidade_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$tituloProfissional = trim((string) ($_POST['titulo_profissional'] ?? ''));
$objetivoProfissional = trim((string) ($_POST['objetivo_profissional'] ?? ''));
$resumoProfissional = trim((string) ($_POST['resumo_profissional'] ?? ''));
$erros = [];

if (strlen($telefone) !== 11) $erros[] = 'Informe um celular com DDD e nove dígitos.';
if (strlen($cep) !== 8) $erros[] = 'Informe um CEP com oito dígitos.';
if ($logradouro === '') $erros[] = 'Informe o logradouro.';
if ($numero === '') $erros[] = 'Informe o número do endereço.';
if ($bairro === '') $erros[] = 'Informe o bairro.';
if ($tituloProfissional === '') $erros[] = 'Informe seu título profissional.';
if (mb_strlen($tituloProfissional) > 150) $erros[] = 'O título profissional deve ter no máximo 150 caracteres.';
if (mb_strlen($objetivoProfissional) > 600) $erros[] = 'O objetivo profissional deve ter no máximo 600 caracteres.';
if (mb_strlen($resumoProfissional) > 1200) $erros[] = 'O resumo profissional deve ter no máximo 1.200 caracteres.';

$cidade = $pdo->prepare("SELECT id FROM cidades WHERE id = ? AND uf = 'MG' AND codigo_ibge IS NOT NULL LIMIT 1");
$cidade->execute([$cidadeId]);
if (!$cidade->fetch()) $erros[] = 'Selecione uma cidade válida.';

if ($erros) {
    definirFlash('erros', $erros);
    redirecionar('candidato/dados-pessoais.php');
}

try {
    $pdo->beginTransaction();
    $atualizar = $pdo->prepare(
        'UPDATE candidatos SET telefone = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade_id = ? WHERE id = ?'
    );
    $atualizar->execute([$telefone, $cep, $logradouro, $numero, $complemento ?: null, $bairro, $cidadeId, $candidatoId]);

    $salvarCurriculo = $pdo->prepare(
        'INSERT INTO curriculos (candidato_id, titulo_profissional, objetivo_profissional, resumo_profissional, visivel)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            titulo_profissional = VALUES(titulo_profissional),
            objetivo_profissional = VALUES(objetivo_profissional),
            resumo_profissional = VALUES(resumo_profissional)'
    );
    $salvarCurriculo->execute([
        $candidatoId,
        $tituloProfissional,
        $objetivoProfissional !== '' ? $objetivoProfissional : null,
        $resumoProfissional !== '' ? $resumoProfissional : null,
    ]);
    $pdo->commit();
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao atualizar dados do candidato: ' . $erro->getMessage());
    definirFlash('erros', ['Não foi possível salvar seus dados agora. Tente novamente.']);
    redirecionar('candidato/dados-pessoais.php');
}

definirFlash('sucesso', 'Dados pessoais e perfil profissional atualizados com sucesso.');
redirecionar('candidato/dados-pessoais.php');
