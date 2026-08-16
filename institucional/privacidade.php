<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/funcoes.php';
$tituloPagina = 'Política de Privacidade';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main class="container py-5">
    <div class="row justify-content-center"><div class="col-xl-9">
        <div class="mb-4"><p class="text-primary fw-semibold mb-1">Transparência e proteção de dados</p><h1 class="display-6">Política de Privacidade</h1><p class="text-secondary">Última atualização: 6 de agosto de 2026.</p></div>
        <div class="alert alert-info"><strong>Projeto acadêmico demonstrativo.</strong> Antes de uma implantação oficial, o órgão responsável deverá validar este documento, identificar formalmente o controlador e o encarregado, definir as bases legais e os prazos de retenção e avaliar a infraestrutura de hospedagem.</div>
        <article class="card shadow-sm border-0"><div class="card-body p-4 p-lg-5">
            <h2 class="h4">1. Sobre o Emprega+</h2><p>O Emprega+ Juiz de Fora é um sistema de banco de currículos e empregabilidade que conecta candidatos a empresas autorizadas e fornece indicadores agregados para a gestão municipal. Nesta versão, o sistema possui finalidade acadêmica e demonstrativa.</p>
            <h2 class="h4 mt-4">2. Quem trata os dados</h2><p>Na eventual implantação oficial, o controlador será o órgão ou entidade municipal que determinar as finalidades e os meios do tratamento. O provedor de hospedagem e outros fornecedores poderão atuar como operadores, conforme contrato e instruções do controlador. O canal provisório do projeto é <a href="mailto:empregaplusjf@gmail.com">empregaplusjf@gmail.com</a>.</p>
            <h2 class="h4 mt-4">3. Dados tratados</h2>
            <ul><li><strong>Candidatos:</strong> nome, CPF, nascimento, telefone, e-mail, cidade, endereço, credenciais protegidas, formação, cursos, experiências, competências, idiomas e situação profissional.</li><li><strong>Empresas:</strong> CNPJ, razão social, nome fantasia, responsável, telefone, e-mail, endereço, credenciais protegidas, status de aprovação e registros de seleção.</li><li><strong>Administradores:</strong> nome, e-mail, perfil de acesso, status e último acesso.</li><li><strong>Dados técnicos:</strong> identificadores de sessão, registros de erro e informações necessárias à segurança e ao funcionamento.</li></ul>
            <h2 class="h4 mt-4">4. Finalidades</h2><ul><li>criar e manter o currículo digital do candidato;</li><li>permitir a busca de perfis por empresas previamente autorizadas;</li><li>possibilitar contato profissional entre empresa e candidato;</li><li>registrar seleções e contratações informadas;</li><li>produzir estatísticas e relatórios para políticas de empregabilidade;</li><li>prevenir fraudes, controlar acessos e proteger o sistema;</li><li>cumprir obrigações legais e atender direitos dos titulares.</li></ul>
            <h2 class="h4 mt-4">5. Base legal e consentimento</h2><p>O cadastro registra a manifestação do candidato para divulgação do perfil profissional a empresas autorizadas. Em uma operação pública oficial, o órgão responsável deverá documentar a hipótese legal adequada a cada atividade, observando a LGPD, especialmente as regras aplicáveis ao Poder Público. Consentimento não deve ser utilizado de forma genérica quando outra hipótese legal for a correta.</p>
            <h2 class="h4 mt-4">6. Quem pode visualizar</h2><p>Empresas ativas e aprovadas podem visualizar dados de contato, cidade e informações profissionais de currículos visíveis. O sistema não exibe às empresas CPF, senha, CEP ou endereço residencial completo. Administradores autorizados acessam informações necessárias à gestão, fiscalização e elaboração de indicadores.</p>
            <h2 class="h4 mt-4">7. Compartilhamento e hospedagem</h2><p>Os dados não devem ser comercializados. O compartilhamento ocorre somente para as finalidades informadas, com empresas autorizadas, áreas administrativas competentes e fornecedores indispensáveis. Como o protótipo utiliza hospedagem de terceiros, a localização dos servidores, eventual transferência internacional e as garantias contratuais devem ser avaliadas antes da implantação oficial.</p>
            <h2 class="h4 mt-4">8. Retenção e eliminação</h2><p>Os dados devem ser mantidos pelo tempo necessário às finalidades e às obrigações legais. A política oficial de retenção deverá estabelecer prazos para contas inativas, registros de seleção, cópias de segurança e logs. Pedidos de eliminação serão analisados considerando a base legal e as hipóteses de conservação previstas em lei.</p>
            <h2 class="h4 mt-4">9. Segurança</h2><p>O sistema utiliza senhas com hash, consultas preparadas, proteção CSRF, sessões com cookies HttpOnly e SameSite, separação de perfis e controle de aprovação. Nenhum sistema é totalmente imune; incidentes deverão ser avaliados e comunicados conforme os procedimentos legais e institucionais aplicáveis.</p>
            <h2 class="h4 mt-4">10. Cookies</h2><p>São utilizados cookies estritamente necessários para manter a sessão autenticada e proteger o acesso. O protótipo não utiliza cookies publicitários ou de rastreamento comportamental.</p>
            <h2 class="h4 mt-4">11. Direitos do titular</h2><p>O titular pode solicitar confirmação e acesso, correção, informação sobre compartilhamento, oposição quando cabível, revisão de decisões automatizadas e anonimização, bloqueio ou eliminação nas hipóteses legais. Quando o tratamento depender de consentimento, poderá solicitar sua revogação. A solicitação deverá ser encaminhada primeiro ao controlador pelo canal oficial que for designado.</p>
            <h2 class="h4 mt-4">12. Atualizações</h2><p>Esta política poderá ser revisada para refletir mudanças legais, institucionais ou técnicas. A versão e a data de atualização permanecerão disponíveis nesta página.</p>
            <h2 class="h4 mt-4">13. Referências</h2><ul><li><a href="https://www.gov.br/anpd/pt-br/assuntos/titular-de-dados-1" target="_blank" rel="noopener noreferrer">Direitos dos titulares — ANPD</a></li><li><a href="https://www.gov.br/anpd/pt-br/centrais-de-conteudo/materiais-educativos-e-publicacoes/guia_orientativo_tratamento_de_dados_pessoais_pelo_poder_publico" target="_blank" rel="noopener noreferrer">Tratamento de dados pessoais pelo Poder Público — ANPD</a></li></ul>
        </div></article>
    </div></div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
