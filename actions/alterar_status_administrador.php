<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
exigirAdministradorMaster();
if($_SERVER['REQUEST_METHOD']!=='POST'||!validarTokenCsrf($_POST['csrf_token']??null))redirecionar('admin/administradores.php');
$usuarioId=filter_var($_POST['usuario_id']??null,FILTER_VALIDATE_INT)?:0;$status=(string)($_POST['status']??'');
if($usuarioId<=0||!in_array($status,['ativo','bloqueado'],true)){definirFlash('erros',['Alteração inválida.']);redirecionar('admin/administradores.php');}
$q=$pdo->prepare("UPDATE usuarios u INNER JOIN administradores a ON a.usuario_id=u.id SET u.status=? WHERE u.id=? AND u.perfil='administrador' AND a.nivel='gestor'");$q->execute([$status,$usuarioId]);
definirFlash($q->rowCount()?'sucesso':'erros',$q->rowCount()?'Status administrativo atualizado.':['Administrador gestor não encontrado.']);redirecionar('admin/administradores.php');
