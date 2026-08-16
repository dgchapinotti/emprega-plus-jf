<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';

exigirCandidato();

$tituloPagina = 'Painel do candidato';
$mensagemSucesso = obterFlash('sucesso');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success" role="alert"><?= escapar((string) $mensagemSucesso) ?></div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Área do candidato</p>
            <h1 class="h2 mb-1">Olá, <?= escapar((string) ($_SESSION['nome'] ?? 'candidato')) ?>!</h1>
            <p class="text-secondary mb-0">Complete as etapas abaixo para publicar seu currículo digital.</p>
        </div>
        <span class="badge text-bg-warning fs-6 align-self-start">Currículo em construção</span>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-3"><div class="card feature-card shadow-sm p-4"><i class="fa-solid fa-address-card feature-icon text-primary mb-3" aria-hidden="true"></i><h2 class="h5">Dados pessoais</h2><p class="text-secondary">Revise seus dados e complete seu endereço.</p><a class="btn btn-outline-primary mt-auto" href="<?= url('candidato/dados-pessoais.php') ?>">Acessar</a></div></div>
        <div class="col-md-6 col-xl-3"><div class="card feature-card shadow-sm p-4"><i class="fa-solid fa-graduation-cap feature-icon text-success mb-3" aria-hidden="true"></i><h2 class="h5">Formações</h2><p class="text-secondary">Adicione sua escolaridade e formação acadêmica.</p><a class="btn btn-outline-primary mt-auto" href="<?= url('candidato/formacoes.php') ?>">Acessar</a></div></div>
        <div class="col-md-6 col-xl-3"><div class="card feature-card shadow-sm p-4"><i class="fa-solid fa-certificate feature-icon text-info mb-3" aria-hidden="true"></i><h2 class="h5">Cursos</h2><p class="text-secondary">Registre cursos complementares e qualificações.</p><a class="btn btn-outline-primary mt-auto" href="<?= url('candidato/cursos.php') ?>">Acessar</a></div></div>
        <div class="col-md-6 col-xl-3"><div class="card feature-card shadow-sm p-4"><i class="fa-solid fa-briefcase feature-icon text-warning mb-3" aria-hidden="true"></i><h2 class="h5">Experiência profissional</h2><p class="text-secondary">Conte às empresas sobre sua trajetória.</p><a class="btn btn-outline-primary mt-auto" href="<?= url('candidato/experiencias.php') ?>">Acessar</a></div></div>

        <div class="col-12">
            <div class="card shadow-sm border-0 p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <div class="text-primary"><i class="fa-solid fa-file-lines fa-3x" aria-hidden="true"></i></div>
                    <div class="flex-grow-1"><h2 class="h5 mb-1">Visualizar meu currículo</h2><p class="text-secondary mb-0">Confira como estão organizados todos os dados que você já cadastrou.</p></div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="<?= url('candidato/curriculo.php') ?>"><i class="fa-solid fa-eye me-2" aria-hidden="true"></i>Ver currículo completo</a>
                        <a class="btn btn-success" href="<?= url('candidato/baixar-curriculo.php') ?>"><i class="fa-solid fa-file-pdf me-2" aria-hidden="true"></i>Baixar PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
