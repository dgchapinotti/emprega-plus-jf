<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/conexao.php';

$candidatoId = obterCandidatoId($pdo);
$consulta = $pdo->prepare(
    'SELECT c.*, u.email, cu.titulo_profissional, cu.objetivo_profissional, cu.resumo_profissional
     FROM candidatos c
     INNER JOIN usuarios u ON u.id = c.usuario_id
     LEFT JOIN curriculos cu ON cu.candidato_id = c.id
     WHERE c.id = ? LIMIT 1'
);
$consulta->execute([$candidatoId]);
$candidato = $consulta->fetch();

if (!$candidato) {
    redirecionar('candidato/painel.php');
}

$cidades = $pdo->query(
    "SELECT id, nome, uf, codigo_ibge FROM cidades
     WHERE uf = 'MG' AND codigo_ibge IS NOT NULL
     ORDER BY (nome = 'Juiz de Fora') DESC, nome"
)->fetchAll();

$cpfFormatado = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $candidato['cpf']);
$sucesso = obterFlash('sucesso');
$erros = obterFlash('erros', []);
$tituloPagina = 'Dados pessoais e endereço';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Currículo digital</p>
            <h1 class="h2 mb-1">Dados pessoais e endereço</h1>
            <p class="text-secondary mb-0">Revise seus dados e mantenha seu endereço atualizado.</p>
        </div>
        <a href="<?= url('candidato/painel.php') ?>" class="btn btn-outline-secondary align-self-start">Voltar ao painel</a>
    </div>

    <?php if ($sucesso): ?><div class="alert alert-success" role="alert"><?= escapar((string) $sucesso) ?></div><?php endif; ?>
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($erros as $erro): ?><li><?= escapar((string) $erro) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-lg-5">
            <form action="<?= url('actions/atualizar_dados_pessoais.php') ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <h2 class="h5 mb-3">Identificação</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="form-label">Nome completo</label><input class="form-control" value="<?= escapar($candidato['nome_completo']) ?>" disabled></div>
                    <div class="col-md-3"><label class="form-label">CPF</label><input class="form-control" value="<?= escapar((string) $cpfFormatado) ?>" disabled></div>
                    <div class="col-md-3"><label class="form-label">Data de nascimento</label><input class="form-control" value="<?= date('d/m/Y', strtotime($candidato['data_nascimento'])) ?>" disabled></div>
                    <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" value="<?= escapar($candidato['email']) ?>" disabled><div class="form-text">Altere o e-mail em Minha conta.</div></div>
                    <div class="col-md-6"><label for="telefone" class="form-label">Telefone</label><input type="tel" id="telefone" name="telefone" class="form-control" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" required value="<?= escapar($candidato['telefone']) ?>"></div>
                </div>

                <h2 class="h5 mb-3">Perfil profissional</h2>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="titulo_profissional" class="form-label">Título profissional</label>
                        <input type="text" id="titulo_profissional" name="titulo_profissional" class="form-control" maxlength="150" required placeholder="Ex.: Auxiliar Administrativo" value="<?= escapar((string) $candidato['titulo_profissional']) ?>">
                        <div class="form-text">Use o cargo ou a área profissional pela qual deseja ser encontrado.</div>
                    </div>
                    <div class="col-12">
                        <label for="objetivo_profissional" class="form-label">Objetivo profissional</label>
                        <textarea id="objetivo_profissional" name="objetivo_profissional" class="form-control" rows="3" maxlength="600" placeholder="Ex.: Busco uma oportunidade na área administrativa para aplicar meus conhecimentos e continuar me desenvolvendo."><?= escapar((string) $candidato['objetivo_profissional']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label for="resumo_profissional" class="form-label">Conte sobre você profissionalmente</label>
                        <textarea id="resumo_profissional" name="resumo_profissional" class="form-control" rows="5" maxlength="1200" placeholder="Fale sobre suas principais qualidades, seu modo de trabalhar, conhecimentos, realizações e objetivos profissionais."><?= escapar((string) $candidato['resumo_profissional']) ?></textarea>
                        <div class="form-text">Evite informações íntimas ou familiares. Escreva de forma objetiva, destacando responsabilidade, organização, comunicação e outras qualidades relacionadas ao trabalho.</div>
                    </div>
                </div>

                <h2 class="h5 mb-3">Endereço</h2>
                <div class="row g-3">
                    <div class="col-md-4"><label for="cep" class="form-label">CEP</label><input type="text" id="cep" name="cep" class="form-control" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="[0-9]{5}-[0-9]{3}" placeholder="00000-000" required value="<?= escapar((string) $candidato['cep']) ?>"><div id="cep_status" class="form-text" aria-live="polite"></div></div>
                    <div class="col-md-8"><label for="logradouro" class="form-label">Logradouro</label><input type="text" id="logradouro" name="logradouro" class="form-control" maxlength="180" required value="<?= escapar((string) $candidato['logradouro']) ?>"></div>
                    <div class="col-md-3"><label for="numero" class="form-label">Número</label><input type="text" id="numero" name="numero" class="form-control" maxlength="20" required value="<?= escapar((string) $candidato['numero']) ?>"></div>
                    <div class="col-md-4"><label for="complemento" class="form-label">Complemento</label><input type="text" id="complemento" name="complemento" class="form-control" maxlength="100" value="<?= escapar((string) $candidato['complemento']) ?>"></div>
                    <div class="col-md-5"><label for="bairro" class="form-label">Bairro</label><input type="text" id="bairro" name="bairro" class="form-control" maxlength="120" required value="<?= escapar((string) $candidato['bairro']) ?>"></div>
                    <div class="col-12"><label for="cidade_id" class="form-label">Cidade</label><select id="cidade_id" name="cidade_id" class="form-select" required><?php foreach ($cidades as $cidade): ?><option value="<?= (int) $cidade['id'] ?>" data-ibge="<?= escapar((string) $cidade['codigo_ibge']) ?>" <?= (int) $candidato['cidade_id'] === (int) $cidade['id'] ? 'selected' : '' ?>><?= escapar($cidade['nome'] . ' - ' . $cidade['uf']) ?></option><?php endforeach; ?></select></div>
                </div>
                <button type="submit" class="btn btn-success btn-lg mt-4">Salvar dados pessoais</button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
