<?php

$tituloPagina = 'Início';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/menu.php';
?>

<main>
    <section class="hero">
        <div class="container text-center">
            <p class="text-uppercase fw-semibold mb-3">Emprega+ Juiz de Fora</p>
            <h1>Sistema Municipal de Banco de Currículos e Empregabilidade</h1>
            <p>Conectando talentos às oportunidades.</p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
                <a href="<?= url('candidato/cadastro.php') ?>" class="btn btn-light btn-lg px-4">
                    <i class="fa-solid fa-file-signature me-2" aria-hidden="true"></i>Cadastrar currículo
                </a>
                <a href="<?= url('empresa/login.php') ?>" class="btn btn-outline-light btn-lg px-4">
                    <i class="fa-solid fa-building me-2" aria-hidden="true"></i>Sou empresa
                </a>
            </div>
        </div>
    </section>

    <section class="container py-5" aria-labelledby="recursos-titulo">
        <div class="text-center mb-5">
            <h2 id="recursos-titulo" class="fw-bold">Oportunidades para todos</h2>
            <p class="text-secondary mb-0">Uma plataforma para aproximar profissionais, empresas e o poder público.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <article class="card feature-card shadow-sm p-4 text-center">
                    <i class="fa-solid fa-user feature-icon text-primary mb-3" aria-hidden="true"></i>
                    <h3 class="h4">Candidatos</h3>
                    <p class="text-secondary mb-0">Crie seu currículo digital e encontre novas oportunidades.</p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="card feature-card shadow-sm p-4 text-center">
                    <i class="fa-solid fa-building feature-icon text-success mb-3" aria-hidden="true"></i>
                    <h3 class="h4">Empresas</h3>
                    <p class="text-secondary mb-0">Encontre profissionais e publique oportunidades de trabalho.</p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="card feature-card shadow-sm p-4 text-center">
                    <i class="fa-solid fa-chart-column feature-icon text-warning mb-3" aria-hidden="true"></i>
                    <h3 class="h4">Prefeitura</h3>
                    <p class="text-secondary mb-0">Use indicadores para apoiar políticas públicas de emprego.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
