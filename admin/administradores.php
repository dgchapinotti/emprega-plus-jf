<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

exigirAdministradorMaster();
$administradores = $pdo->query(
    "SELECT u.id,u.nome,u.email,u.status,u.ultimo_acesso,a.nivel,a.criado_em,
            criador.nome AS criado_por_nome
     FROM administradores a
     INNER JOIN usuarios u ON u.id=a.usuario_id
     LEFT JOIN usuarios criador ON criador.id=a.criado_por
     ORDER BY (a.nivel='master') DESC,u.nome"
)->fetchAll();
$tituloPagina = 'Administradores';
$mensagemSucesso = obterFlash('sucesso');
$erros = obterFlash('erros', []);
$dados = obterFlash('dados', []);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Segurança e acessos</p><h1 class="h2 mb-1">Administradores</h1><p class="text-secondary mb-0">Crie acessos individuais para servidores autorizados.</p></div><a href="<?= url('admin/dashboard.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a></div>
    <?php if ($mensagemSucesso): ?><div class="alert alert-success"><?= escapar((string)$mensagemSucesso) ?></div><?php endif; ?>
    <?php if ($erros): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($erros as $erro): ?><li><?= escapar((string)$erro) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5"><div class="card shadow-sm border-0"><div class="card-body p-4"><h2 class="h5 mb-3">Novo administrador gestor</h2><form action="<?= url('actions/cadastrar_administrador.php') ?>" method="post"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><div class="mb-3"><label for="nome" class="form-label">Nome completo</label><input type="text" id="nome" name="nome" class="form-control" maxlength="150" required value="<?= escapar((string)($dados['nome'] ?? '')) ?>"></div><div class="mb-3"><label for="email" class="form-label">E-mail institucional</label><input type="email" id="email" name="email" class="form-control" maxlength="190" required value="<?= escapar((string)($dados['email'] ?? '')) ?>"></div><div class="mb-3"><label for="senha" class="form-label">Senha inicial</label><input type="password" id="senha" name="senha" class="form-control" minlength="8" autocomplete="new-password" required><div class="form-text">Use pelo menos 8 caracteres e entregue a senha de forma segura.</div></div><div class="mb-4"><label for="confirmar_senha" class="form-label">Confirmar senha</label><input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="8" autocomplete="new-password" required></div><button class="btn btn-dark w-100"><i class="fa-solid fa-user-plus me-2"></i>Criar acesso gestor</button></form></div></div></div>
        <div class="col-lg-7"><div class="card shadow-sm border-0"><div class="card-body p-4"><h2 class="h5 mb-3">Acessos cadastrados</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Administrador</th><th>Nível</th><th>Status</th><th class="text-end">Ação</th></tr></thead><tbody><?php foreach($administradores as $admin): ?><tr><td><strong><?= escapar($admin['nome']) ?></strong><br><small class="text-secondary"><?= escapar($admin['email']) ?></small></td><td><span class="badge <?= $admin['nivel']==='master'?'text-bg-dark':'text-bg-primary' ?>"><?= escapar(ucfirst($admin['nivel'])) ?></span></td><td><span class="badge <?= $admin['status']==='ativo'?'text-bg-success':'text-bg-secondary' ?>"><?= escapar(ucfirst($admin['status'])) ?></span></td><td class="text-end"><?php if($admin['nivel']!=='master'): ?><form action="<?= url('actions/alterar_status_administrador.php') ?>" method="post"><input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="usuario_id" value="<?= (int)$admin['id'] ?>"><input type="hidden" name="status" value="<?= $admin['status']==='ativo'?'bloqueado':'ativo' ?>"><button class="btn btn-sm <?= $admin['status']==='ativo'?'btn-outline-danger':'btn-outline-success' ?>"><?= $admin['status']==='ativo'?'Bloquear':'Ativar' ?></button></form><?php else: ?><span class="text-secondary small">Protegido</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
