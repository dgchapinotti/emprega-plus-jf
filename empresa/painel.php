<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$empresaId = obterEmpresaId($pdo);
$consulta = $pdo->prepare(
    'SELECT e.*, u.email, c.nome AS cidade, c.uf
     FROM empresas e
     INNER JOIN usuarios u ON u.id = e.usuario_id
     INNER JOIN cidades c ON c.id = e.cidade_id
     WHERE e.id = ? LIMIT 1'
);
$consulta->execute([$empresaId]);
$empresa = $consulta->fetch();

if (!$empresa) {
    $_SESSION = [];
    session_destroy();
    redirecionar('empresa/login.php');
}

$tituloPagina = 'Painel da empresa';
$mensagemSucesso = obterFlash('sucesso');
$cnpjFormatado = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $empresa['cnpj']);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <?php if ($mensagemSucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string) $mensagemSucesso) ?></div><?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Área da empresa</p>
            <h1 class="h2 mb-1">Olá, <?= escapar((string) $empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>!</h1>
            <p class="text-secondary mb-0">Seu cadastro está aprovado e o acesso empresarial está ativo.</p>
        </div>
        <span class="badge text-bg-success fs-6 align-self-start"><i class="fa-solid fa-circle-check me-1"></i>Empresa aprovada</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4"><i class="fa-solid fa-building text-primary me-2"></i>Dados da empresa</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Razão social</dt><dd class="col-sm-8"><?= escapar($empresa['razao_social']) ?></dd>
                        <dt class="col-sm-4">CNPJ</dt><dd class="col-sm-8"><?= escapar((string) $cnpjFormatado) ?></dd>
                        <dt class="col-sm-4">Responsável</dt><dd class="col-sm-8"><?= escapar($empresa['responsavel_nome']) ?></dd>
                        <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><?= escapar($empresa['email']) ?></dd>
                        <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8"><?= escapar($empresa['telefone']) ?></dd>
                        <dt class="col-sm-4">Cidade</dt><dd class="col-sm-8"><?= escapar($empresa['cidade'] . ' - ' . $empresa['uf']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <i class="fa-solid fa-magnifying-glass-chart fa-3x text-primary mb-3"></i>
                    <h2 class="h5">Pesquisa de profissionais</h2>
                    <p class="text-secondary">Localize candidatos por cidade, formação, experiência e competências.</p>
                    <a class="btn btn-primary mt-auto" href="<?= url('empresa/pesquisar.php') ?>">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Pesquisar profissionais
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
