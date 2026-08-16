<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$candidatoId = obterCandidatoId($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar('candidato/formacoes.php');
if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) { definirFlash('erros', ['Sua sessão expirou.']); redirecionar('candidato/formacoes.php'); }

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$nivel = (string) ($_POST['nivel'] ?? '');
$instituicao = trim((string) ($_POST['instituicao'] ?? ''));
$curso = trim((string) ($_POST['curso'] ?? ''));
$inicio = (string) ($_POST['data_inicio'] ?? '');
$conclusao = (string) ($_POST['data_conclusao'] ?? '');
$cursando = ($_POST['cursando'] ?? null) === '1';
$niveis = ['fundamental_incompleto','fundamental_completo','medio_incompleto','medio_completo','tecnico','superior_incompleto','superior_completo','pos_graduacao','mestrado','doutorado'];
$erros = [];

if (!in_array($nivel, $niveis, true)) $erros[] = 'Selecione um nível de escolaridade válido.';
if ($instituicao === '') $erros[] = 'Informe a instituição.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) $erros[] = 'Informe uma data de início válida.';
if (!$cursando && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $conclusao)) $erros[] = 'Informe a data de conclusão ou marque que está cursando.';
if (!$cursando && $inicio && $conclusao && $conclusao < $inicio) $erros[] = 'A conclusão não pode ser anterior ao início.';

if ($erros) { definirFlash('erros', $erros); redirecionar('candidato/formacoes.php' . ($id ? '?editar=' . $id : '')); }
$conclusao = $cursando ? null : $conclusao;

if ($id) {
    $salvar = $pdo->prepare('UPDATE formacoes SET nivel=?, instituicao=?, curso=?, data_inicio=?, data_conclusao=?, cursando=? WHERE id=? AND candidato_id=?');
    $salvar->execute([$nivel,$instituicao,$curso ?: null,$inicio,$conclusao,(int)$cursando,$id,$candidatoId]);
    if ($salvar->rowCount() === 0) {
        $verificar = $pdo->prepare('SELECT id FROM formacoes WHERE id=? AND candidato_id=?'); $verificar->execute([$id,$candidatoId]);
        if (!$verificar->fetch()) { definirFlash('erros', ['Formação não encontrada.']); redirecionar('candidato/formacoes.php'); }
    }
    definirFlash('sucesso', 'Formação atualizada com sucesso.');
} else {
    $salvar = $pdo->prepare('INSERT INTO formacoes (candidato_id,nivel,instituicao,curso,data_inicio,data_conclusao,cursando) VALUES (?,?,?,?,?,?,?)');
    $salvar->execute([$candidatoId,$nivel,$instituicao,$curso ?: null,$inicio,$conclusao,(int)$cursando]);
    definirFlash('sucesso', 'Formação adicionada com sucesso.');
}
redirecionar('candidato/formacoes.php');

