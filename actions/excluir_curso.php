<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/funcoes.php'; require_once __DIR__ . '/../includes/conexao.php';
$candidatoId=obterCandidatoId($pdo); if($_SERVER['REQUEST_METHOD']!=='POST'||!validarTokenCsrf($_POST['csrf_token']??null))redirecionar('candidato/cursos.php');
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT)?:0; $s=$pdo->prepare('DELETE FROM cursos WHERE id=? AND candidato_id=?');$s->execute([$id,$candidatoId]);definirFlash($s->rowCount()?'sucesso':'erros',$s->rowCount()?'Curso excluído com sucesso.':['Curso não encontrado.']);redirecionar('candidato/cursos.php');
