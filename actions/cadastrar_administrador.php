<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';
exigirAdministradorMaster();
if($_SERVER['REQUEST_METHOD']!=='POST') redirecionar('admin/administradores.php');
if(!validarTokenCsrf($_POST['csrf_token']??null)){definirFlash('erros',['Sua sessão expirou.']);redirecionar('admin/administradores.php');}
$nome=trim((string)($_POST['nome']??''));$email=strtolower(trim((string)($_POST['email']??'')));$senha=(string)($_POST['senha']??'');$confirmar=(string)($_POST['confirmar_senha']??'');$erros=[];
if(mb_strlen($nome)<3)$erros[]='Informe o nome completo.';if(!filter_var($email,FILTER_VALIDATE_EMAIL))$erros[]='Informe um e-mail válido.';if(strlen($senha)<8)$erros[]='A senha deve ter pelo menos 8 caracteres.';if($senha!==$confirmar)$erros[]='A confirmação da senha não corresponde.';
if($erros){definirFlash('erros',$erros);definirFlash('dados',['nome'=>$nome,'email'=>$email]);redirecionar('admin/administradores.php');}
try{$pdo->beginTransaction();$q=$pdo->prepare('SELECT id FROM usuarios WHERE email=? LIMIT 1');$q->execute([$email]);if($q->fetch())throw new DomainException('Já existe uma conta com este e-mail.');$q=$pdo->prepare("INSERT INTO usuarios(nome,email,senha_hash,perfil,status) VALUES(?,?,?,'administrador','ativo')");$q->execute([$nome,$email,password_hash($senha,PASSWORD_DEFAULT)]);$id=(int)$pdo->lastInsertId();$q=$pdo->prepare("INSERT INTO administradores(usuario_id,nivel,criado_por) VALUES(?,'gestor',?)");$q->execute([$id,(int)$_SESSION['usuario_id']]);$pdo->commit();definirFlash('sucesso','Administrador gestor criado com sucesso.');}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();definirFlash('erros',[$e->getMessage()]);}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Erro ao criar administrador: '.$e->getMessage());definirFlash('erros',['Não foi possível criar o acesso.']);}redirecionar('admin/administradores.php');
