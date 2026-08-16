<?php

require_once __DIR__ . '/funcoes.php';
iniciarSessao();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm" aria-label="Navegação principal">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url() ?>">
            <i class="fa-solid fa-briefcase me-2" aria-hidden="true"></i>Emprega+
            <span class="brand-city">Juiz de Fora</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url() ?>">Início</a>
                </li>
                <?php if (candidatoAutenticado()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('candidato/painel.php') ?>">Meu painel</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('candidato/cadastro.php') ?>">Candidato</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url(empresaAutenticada() ? 'empresa/painel.php' : 'empresa/login.php') ?>">
                        <?= empresaAutenticada() ? 'Painel da empresa' : 'Empresas' ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url(administradorAutenticado() ? 'admin/dashboard.php' : 'admin/login.php') ?>">
                        <?= administradorAutenticado() ? 'Painel da Prefeitura' : 'Prefeitura' ?>
                    </a>
                </li>
                <?php if (usuarioAutenticado()): ?>
                    <li class="nav-item ms-lg-2">
                        <form action="<?= url('actions/logout.php') ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                            <button type="submit" class="btn btn-outline-light btn-sm">
                                <i class="fa-solid fa-right-from-bracket me-1" aria-hidden="true"></i>Sair
                            </button>
                        </form>
                    </li>
                    <?php if (candidatoAutenticado()): ?>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link account-icon" href="<?= url('candidato/conta.php') ?>"
                                aria-label="Minha conta" title="Minha conta">
                                <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                                <span class="visually-hidden">Minha conta</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
