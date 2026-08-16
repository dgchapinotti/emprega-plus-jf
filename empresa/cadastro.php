<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

iniciarSessao();

if (usuarioAutenticado()) {
    redirecionar('');
}

$tituloPagina = 'Cadastro de empresa';
$erros = obterFlash('erros', []);
$sucesso = obterFlash('sucesso');
$dados = obterFlash('dados', []);
$cidades = $pdo->query(
    "SELECT id, nome, uf, codigo_ibge FROM cidades
     WHERE uf = 'MG' AND codigo_ibge IS NOT NULL
     ORDER BY (nome = 'Juiz de Fora') DESC, nome"
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h1 class="h3 mb-1">Cadastre sua empresa</h1>
                    <p class="mb-0 opacity-75">Após a análise da Prefeitura, sua empresa poderá pesquisar perfis profissionais.</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success" role="alert"><?= escapar((string) $sucesso) ?></div>
                    <?php endif; ?>
                    <?php if ($erros): ?>
                        <div class="alert alert-danger" role="alert">
                            <p class="fw-semibold mb-2">Confira os dados informados:</p>
                            <ul class="mb-0"><?php foreach ($erros as $erro): ?><li><?= escapar((string) $erro) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('actions/cadastrar_empresa.php') ?>" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">

                        <h2 class="h5 mb-3">Dados da empresa</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label for="cnpj" class="form-label">CNPJ</label><input type="text" id="cnpj" name="cnpj" class="form-control" inputmode="numeric" maxlength="18" placeholder="00.000.000/0000-00" required value="<?= escapar((string) ($dados['cnpj'] ?? '')) ?>"></div>
                            <div class="col-md-8"><label for="razao_social" class="form-label">Razão social</label><input type="text" id="razao_social" name="razao_social" class="form-control" maxlength="180" required value="<?= escapar((string) ($dados['razao_social'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label for="nome_fantasia" class="form-label">Nome fantasia</label><input type="text" id="nome_fantasia" name="nome_fantasia" class="form-control" maxlength="180" value="<?= escapar((string) ($dados['nome_fantasia'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label for="responsavel_nome" class="form-label">Responsável pela empresa</label><input type="text" id="responsavel_nome" name="responsavel_nome" class="form-control" maxlength="150" autocomplete="name" required value="<?= escapar((string) ($dados['responsavel_nome'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label for="telefone_empresa" class="form-label">Telefone</label><input type="tel" id="telefone_empresa" name="telefone" class="form-control" inputmode="numeric" maxlength="15" placeholder="(32) 99999-9999" autocomplete="tel" required value="<?= escapar((string) ($dados['telefone'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label for="email" class="form-label">E-mail corporativo</label><input type="email" id="email" name="email" class="form-control" maxlength="190" autocomplete="email" required value="<?= escapar((string) ($dados['email'] ?? '')) ?>"></div>
                        </div>

                        <h2 class="h5 mb-3">Endereço</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><label for="cep" class="form-label">CEP</label><input type="text" id="cep" name="cep" class="form-control" inputmode="numeric" autocomplete="postal-code" maxlength="9" placeholder="00000-000" required value="<?= escapar((string) ($dados['cep'] ?? '')) ?>"><div id="cep_status" class="form-text" aria-live="polite"></div></div>
                            <div class="col-md-8"><label for="logradouro" class="form-label">Logradouro</label><input type="text" id="logradouro" name="logradouro" class="form-control" maxlength="180" required value="<?= escapar((string) ($dados['logradouro'] ?? '')) ?>"></div>
                            <div class="col-md-3"><label for="numero" class="form-label">Número</label><input type="text" id="numero" name="numero" class="form-control" maxlength="20" required value="<?= escapar((string) ($dados['numero'] ?? '')) ?>"></div>
                            <div class="col-md-4"><label for="complemento" class="form-label">Complemento</label><input type="text" id="complemento" name="complemento" class="form-control" maxlength="100" value="<?= escapar((string) ($dados['complemento'] ?? '')) ?>"></div>
                            <div class="col-md-5"><label for="bairro" class="form-label">Bairro</label><input type="text" id="bairro" name="bairro" class="form-control" maxlength="120" required value="<?= escapar((string) ($dados['bairro'] ?? '')) ?>"></div>
                            <div class="col-12"><label for="cidade_id" class="form-label">Cidade</label><select id="cidade_id" name="cidade_id" class="form-select" required><option value="">Selecione a cidade</option><?php foreach ($cidades as $cidade): ?><option value="<?= (int) $cidade['id'] ?>" data-ibge="<?= escapar((string) $cidade['codigo_ibge']) ?>" <?= (string) ($dados['cidade_id'] ?? '') === (string) $cidade['id'] ? 'selected' : '' ?>><?= escapar($cidade['nome'] . ' - ' . $cidade['uf']) ?></option><?php endforeach; ?></select></div>
                        </div>

                        <h2 class="h5 mb-3">Dados de acesso</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><label for="senha" class="form-label">Senha</label><input type="password" id="senha" name="senha" class="form-control" minlength="8" autocomplete="new-password" required><div class="form-text">Use pelo menos 8 caracteres.</div></div>
                            <div class="col-md-6"><label for="confirmar_senha" class="form-label">Confirme a senha</label><input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="8" autocomplete="new-password" required></div>
                        </div>

                        <div class="form-check mb-4"><input class="form-check-input" type="checkbox" value="1" id="consentimento" name="consentimento" required><label class="form-check-label" for="consentimento">Declaro que represento esta empresa e concordo com o uso responsável dos dados profissionais disponibilizados pelo sistema.</label></div>
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3"><button type="submit" class="btn btn-success btn-lg px-4"><i class="fa-solid fa-building-circle-check me-2" aria-hidden="true"></i>Solicitar cadastro</button><a href="<?= url('empresa/login.php') ?>">Já possuo cadastro</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
