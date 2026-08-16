<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/funcoes.php'; require_once __DIR__ . '/../includes/conexao.php';
$candidatoId=obterCandidatoId($pdo); if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('candidato/cursos.php');
if(!validarTokenCsrf($_POST['csrf_token']??null)){definirFlash('erros',['Sua sessão expirou.']);redirecionar('candidato/cursos.php');}
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT)?:0; $nome=trim((string)($_POST['nome']??'')); $instituicao=trim((string)($_POST['instituicao']??'')); $carga=filter_var($_POST['carga_horaria']??null,FILTER_VALIDATE_INT)?:0; $ano=filter_var($_POST['ano_conclusao']??null,FILTER_VALIDATE_INT)?:0; $erros=[]; $anoAtual=(int)date('Y');
if($nome==='')$erros[]='Informe o nome do curso.'; if($instituicao==='')$erros[]='Informe a instituição.'; if($carga<1||$carga>65535)$erros[]='Informe uma carga horária válida.'; if($ano<1950||$ano>$anoAtual)$erros[]='Informe um ano de conclusão válido.';
if($erros){definirFlash('erros',$erros);redirecionar('candidato/cursos.php'.($id?'?editar='.$id:''));}
if($id){$s=$pdo->prepare('UPDATE cursos SET nome=?,instituicao=?,carga_horaria=?,ano_conclusao=? WHERE id=? AND candidato_id=?');$s->execute([$nome,$instituicao,$carga,$ano,$id,$candidatoId]);if($s->rowCount()===0){$v=$pdo->prepare('SELECT id FROM cursos WHERE id=? AND candidato_id=?');$v->execute([$id,$candidatoId]);if(!$v->fetch()){definirFlash('erros',['Curso não encontrado.']);redirecionar('candidato/cursos.php');}}definirFlash('sucesso','Curso atualizado com sucesso.');}
else{$s=$pdo->prepare('INSERT INTO cursos(candidato_id,nome,instituicao,carga_horaria,ano_conclusao) VALUES(?,?,?,?,?)');$s->execute([$candidatoId,$nome,$instituicao,$carga,$ano]);definirFlash('sucesso','Curso adicionado com sucesso.');}
redirecionar('candidato/cursos.php');

