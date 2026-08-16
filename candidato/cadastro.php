<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if (candidatoAutenticado()) {
    redirecionar('candidato/painel.php');
}

$tituloPagina = 'Criar conta de candidato';
$erros = obterFlash('erros', []);
$dados = obterFlash('dados', []);
$cidades = $pdo->query(
    "SELECT id, nome, uf
     FROM cidades
     WHERE uf = 'MG' AND codigo_ibge IS NOT NULL
     ORDER BY (nome = 'Juiz de Fora') DESC, nome"
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h1 class="h3 mb-1">Crie sua conta de candidato</h1>
                    <p class="mb-0 opacity-75">Depois do cadastro, você poderá montar seu currículo digital.</p>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <?php if ($erros): ?>
                        <div class="alert alert-danger" role="alert">
                            <p class="fw-semibold mb-2">Confira os dados informados:</p>
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= escapar((string) $erro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('actions/cadastrar_candidato.php') ?>" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">

                        <h2 class="h5 mb-3">Dados pessoais</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="nome_completo" class="form-label">Nome completo</label>
                                <input type="text" id="nome_completo" name="nome_completo" class="form-control"
                                    maxlength="180" autocomplete="name" required
                                    value="<?= escapar((string) ($dados['nome_completo'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" id="cpf" name="cpf" class="form-control" inputmode="numeric"
                                    maxlength="14" pattern="[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}"
                                    autocomplete="off" placeholder="000.000.000-00" required
                                    value="<?= escapar((string) ($dados['cpf'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="data_nascimento" class="form-label">Data de nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control"
                                    max="<?= date('Y-m-d') ?>" required
                                    value="<?= escapar((string) ($dados['data_nascimento'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control"
                                    inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}"
                                    autocomplete="tel" placeholder="(32) 99999-9999" required
                                    value="<?= escapar((string) ($dados['telefone'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    maxlength="190" autocomplete="email" required
                                    value="<?= escapar((string) ($dados['email'] ?? '')) ?>">
                            </div>
                        </div>

                        <h2 class="h5 mb-3">Localização</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="cidade" class="form-label">Cidade</label>
                                <select id="cidade" name="cidade_id" class="form-select" required <?= !$cidades ? 'disabled' : '' ?>>
                                    <option value="">Selecione sua cidade</option>
                                    <?php foreach ($cidades as $cidade): ?>
                                        <option value="<?= (int) $cidade['id'] ?>"
                                            <?= (string) ($dados['cidade_id'] ?? '') === (string) $cidade['id'] ? 'selected' : '' ?>>
                                            <?= escapar($cidade['nome'] . ' - ' . $cidade['uf']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$cidades): ?>
                                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                                        A lista regional de cidades ainda não foi importada.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h2 class="h5 mb-3">Dados de acesso</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" id="senha" name="senha" class="form-control"
                                    minlength="8" autocomplete="new-password" required>
                                <div class="form-text">Use pelo menos 8 caracteres.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmar_senha" class="form-label">Confirme a senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control"
                                    minlength="8" autocomplete="new-password" required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="consentimento" name="consentimento" required>
                            <label class="form-check-label" for="consentimento">
                                Autorizo o tratamento dos meus dados para cadastro e divulgação do perfil profissional
                                às empresas autorizadas, conforme a política de privacidade.
                            </label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            <button type="submit" class="btn btn-success btn-lg px-4">
                                <i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>Criar minha conta
                            </button>
                            <a href="<?= url('candidato/login.php') ?>" class="link-primary">Já tenho uma conta</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
