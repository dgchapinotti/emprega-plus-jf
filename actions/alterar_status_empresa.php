<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
exigirAdministrador();

if($_SERVER['REQUEST_METHOD']!=='POST'||!validarTokenCsrf($_POST['csrf_token']??null)){definirFlash('erro','Sua sessão expirou.');redirecionar('admin/empresas.php');}
$empresaId=filter_var($_POST['empresa_id']??null,FILTER_VALIDATE_INT)?:0;$acao=(string)($_POST['acao']??'');
if($empresaId<=0||!in_array($acao,['aprovar','bloquear','reativar'],true)){definirFlash('erro','Operação inválida.');redirecionar('admin/empresas.php');}

$consulta=$pdo->prepare("SELECT e.usuario_id,u.status FROM empresas e INNER JOIN usuarios u ON u.id=e.usuario_id AND u.perfil='empresa' WHERE e.id=? LIMIT 1");$consulta->execute([$empresaId]);$empresa=$consulta->fetch();
if(!$empresa){definirFlash('erro','Empresa não encontrada.');redirecionar('admin/empresas.php');}

try{$pdo->beginTransaction();
if($acao==='aprovar'){$q=$pdo->prepare("UPDATE usuarios SET status='ativo' WHERE id=?");$q->execute([$empresa['usuario_id']]);$q=$pdo->prepare('UPDATE empresas SET aprovada_em=NOW(),aprovada_por=? WHERE id=?');$q->execute([(int)$_SESSION['usuario_id'],$empresaId]);$mensagem='Empresa aprovada e acesso liberado.';}
elseif($acao==='bloquear'){$q=$pdo->prepare("UPDATE usuarios SET status='bloqueado' WHERE id=?");$q->execute([$empresa['usuario_id']]);$mensagem='Acesso da empresa bloqueado.';}
else{$q=$pdo->prepare("UPDATE usuarios SET status='ativo' WHERE id=?");$q->execute([$empresa['usuario_id']]);$q=$pdo->prepare('UPDATE empresas SET aprovada_em=COALESCE(aprovada_em,NOW()),aprovada_por=COALESCE(aprovada_por,?) WHERE id=?');$q->execute([(int)$_SESSION['usuario_id'],$empresaId]);$mensagem='Acesso da empresa reativado.';}
$pdo->commit();definirFlash('sucesso',$mensagem);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Erro ao alterar empresa: '.$e->getMessage());definirFlash('erro','Não foi possível alterar o acesso da empresa.');}
redirecionar('admin/empresas.php');
