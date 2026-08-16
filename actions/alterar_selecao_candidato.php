<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirEmpresa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('empresa/pesquisar.php');
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirecionar('empresa/pesquisar.php');
}

$empresaId = obterEmpresaId($pdo);
$candidatoId = filter_var($_POST['candidato_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$acao = (string) ($_POST['acao'] ?? '');

if ($candidatoId <= 0 || !in_array($acao, ['selecionar', 'cancelar'], true)) {
    definirFlash('erro', 'Não foi possível alterar a seleção informada.');
    redirecionar('empresa/pesquisar.php');
}

$consulta = $pdo->prepare(
    "SELECT c.id
     FROM candidatos c
     INNER JOIN usuarios u ON u.id = c.usuario_id AND u.status = 'ativo'
     INNER JOIN curriculos cu ON cu.candidato_id = c.id AND cu.visivel = 1
     WHERE c.id = ? LIMIT 1"
);
$consulta->execute([$candidatoId]);

if (!$consulta->fetchColumn()) {
    definirFlash('erro', 'O currículo não está mais disponível.');
    redirecionar('empresa/pesquisar.php');
}

if ($acao === 'selecionar') {
    $salvar = $pdo->prepare(
        "INSERT INTO selecoes_empresas
            (empresa_id, candidato_id, status, selecionado_em)
         VALUES (?, ?, 'selecionado', NOW())
         ON DUPLICATE KEY UPDATE
            status = 'selecionado', selecionado_em = NOW()"
    );
    $salvar->execute([$empresaId, $candidatoId]);
    definirFlash('sucesso', 'Candidato marcado como selecionado. A Prefeitura poderá acompanhar este resultado.');
} else {
    $cancelar = $pdo->prepare(
        "UPDATE selecoes_empresas
         SET status = 'cancelado'
         WHERE empresa_id = ? AND candidato_id = ?"
    );
    $cancelar->execute([$empresaId, $candidatoId]);
    definirFlash('sucesso', 'A seleção deste candidato foi removida.');
}

redirecionar('empresa/candidato.php?id=' . $candidatoId);
