# Documentação do Sistema Emprega+ Juiz de Fora

## 1. Objetivo

Centralizar currículos digitais, facilitar a localização de profissionais por empresas autorizadas e produzir informações agregadas para políticas municipais de emprego e qualificação.

## 2. Perfis e permissões

| Perfil | Permissões principais |
|---|---|
| Candidato | Gerenciar conta, dados pessoais, formações, cursos e experiências |
| Empresa pendente | Aguardar análise administrativa |
| Empresa ativa | Pesquisar e visualizar currículos; registrar seleção |
| Administrador gestor | Dashboard, empresas, candidatos, seleções e relatórios |
| Administrador master | Todas as permissões do gestor e gestão de administradores |

## 3. Fluxos principais

### Candidato

Cadastro → autenticação → dados pessoais → formações → cursos → experiências → currículo visível para empresas autorizadas.

### Empresa

Cadastro → status pendente → aprovação pela Prefeitura → autenticação → filtros de pesquisa → currículo → seleção do candidato.

### Prefeitura

Autenticação → dashboard → detalhamento dos indicadores → aprovação de empresas → acompanhamento das seleções → relatórios e gráficos.

## 4. Modelo de dados

Tabelas centrais:

- `usuarios`: autenticação, perfil e status;
- `candidatos`: identificação e contato;
- `empresas`: cadastro empresarial e aprovação;
- `curriculos`: objetivo, resumo, área e visibilidade;
- `formacoes`, `cursos`, `experiencias`: trajetória profissional;
- `competencias`, `candidato_competencias`: habilidades;
- `idiomas`, `candidato_idiomas`: proficiência;
- `cidades`: municípios e códigos IBGE;
- `selecoes_empresas`: selecionados, contratados e cancelados;
- `administradores`: hierarquia master/gestor;
- `recuperacoes_senha`: tokens temporários;
- `areas_profissionais`: classificação de currículos.

As tabelas `vagas` e `candidaturas` permanecem no esquema legado, mas não integram o escopo atual, pois as empresas pesquisam diretamente o banco de currículos.

## 5. Segurança

- Senhas nunca são armazenadas em texto puro.
- Formulários sensíveis usam tokens CSRF.
- Entradas são validadas no servidor.
- Consultas usam parâmetros PDO.
- Sessões separam candidato, empresa e administração.
- Empresas somente acessam currículos após aprovação.
- A empresa não recebe CPF nem endereço residencial completo.
- O administrador master não pode ser bloqueado pela tela de gestores.

## 6. Ambientes

| Ambiente | Endereço | Banco |
|---|---|---|
| Desenvolvimento | `http://localhost/Emprega_JF` | MySQL do XAMPP |
| Demonstração | `https://empregaplusjf.ifree.page` | MySQL do InfinityFree |

Os bancos são independentes. Uma alteração de estrutura ou dado demonstrativo precisa ser aplicada nos dois ambientes.

## 7. Testes finais recomendados

### Candidato

- criar conta com CPF válido;
- impedir CPF/e-mail duplicado;
- testar login, logout e recuperação de senha;
- testar CEP automático;
- incluir, editar e excluir formação, curso e experiência;
- marcar emprego atual e confirmar a exibição para a empresa.

### Empresa

- criar empresa e confirmar status pendente;
- tentar login antes da aprovação;
- aprovar pela Prefeitura e autenticar;
- combinar filtros de pesquisa;
- abrir currículo e registrar seleção;
- remover seleção e confirmar histórico cancelado;
- bloquear empresa e negar novo login.

### Prefeitura

- autenticar como master;
- criar gestor e confirmar que ele não gerencia administradores;
- abrir todos os indicadores;
- aprovar, bloquear e reativar empresa;
- conferir candidato e empresa nas seleções;
- renderizar todos os gráficos;
- baixar e abrir os três PDFs em ambos os ambientes.

## 8. Pendências para implantação oficial

- nomear formalmente controlador, operador e encarregado;
- definir bases legais por finalidade;
- estabelecer política de retenção e descarte;
- avaliar contrato, localização e segurança da hospedagem;
- criar processo de atendimento aos direitos dos titulares;
- definir resposta a incidentes e rotina de backups;
- revisar acessibilidade conforme WCAG/eMAG;
- realizar testes de segurança e carga;
- trocar todas as credenciais demonstrativas;
- obter aprovação jurídica e administrativa dos textos institucionais.

## 9. Backup e publicação

1. Exporte o banco pelo phpMyAdmin.
2. Copie a pasta do projeto sem expor credenciais.
3. Registre a versão e as migrações aplicadas.
4. Teste primeiro no XAMPP.
5. Envie os arquivos ao InfinityFree.
6. Importe as migrações necessárias.
7. Repita o roteiro de testes rápidos em produção.

