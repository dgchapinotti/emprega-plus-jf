<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
$candidatoId = obterCandidatoId($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarTokenCsrf($_POST['csrf_token'] ?? null)) redirecionar('candidato/formacoes.php');
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$excluir = $pdo->prepare('DELETE FROM formacoes WHERE id = ? AND candidato_id = ?');
$excluir->execute([$id, $candidatoId]);
definirFlash($excluir->rowCount() ? 'sucesso' : 'erros', $excluir->rowCount() ? 'Formação excluída com sucesso.' : ['Formação não encontrada.']);
redirecionar('candidato/formacoes.php');
