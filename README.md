# Emprega+ Juiz de Fora

Sistema Municipal de Banco de Currículos e Empregabilidade desenvolvido como projeto acadêmico extensionista.

O sistema permite que candidatos construam um currículo digital, empresas autorizadas pesquisem profissionais e a gestão municipal acompanhe indicadores de empregabilidade.

## Funcionalidades

### Candidato

- cadastro com CPF, e-mail e senha;
- login, logout e recuperação de senha por e-mail;
- atualização de dados pessoais e endereço por CEP;
- múltiplas formações, cursos e experiências;
- indicação de emprego atual;
- currículo digital normalizado.

### Empresa

- cadastro com validação de CNPJ e endereço por CEP;
- acesso somente após aprovação administrativa;
- pesquisa por cidade, área, escolaridade, curso, competência e experiência;
- visualização segura do currículo, sem CPF ou endereço residencial;
- marcação de candidatos selecionados.

### Prefeitura

- administrador master e administradores gestores;
- gestão e aprovação de empresas;
- indicadores detalhados e listas nominais;
- acompanhamento de seleções e contratações;
- gráficos com Chart.js;
- relatórios PDF por período.

## Tecnologias

- PHP 8.3;
- MySQL/MariaDB;
- PDO e consultas preparadas;
- Bootstrap 5;
- Font Awesome;
- JavaScript;
- Chart.js;
- PHPMailer;
- XAMPP para desenvolvimento;
- InfinityFree para demonstração em produção.

## Estrutura

```text
Emprega_JF/
├── actions/          # Processamento seguro de formulários
├── admin/            # Área da Prefeitura
├── assets/           # CSS, JavaScript e imagens
├── candidato/        # Área do candidato
├── database/         # Scripts e migrações SQL
├── docs/             # Documentação e roteiro
├── empresa/          # Área empresarial
├── includes/         # Configuração, conexão e componentes
├── institucional/    # Privacidade e termos
├── vendor/           # Dependências PHP
└── index.php
```

## Instalação local

1. Instale e inicie Apache e MySQL pelo XAMPP.
2. Coloque o projeto em `C:\xampp\htdocs\Emprega_JF` ou utilize uma junction para `F:\Emprega_JF`.
3. Crie o banco `emprega_jf` e importe o esquema e as migrações da pasta `database`.
4. Configure as credenciais locais em `includes/credenciais.php`.
5. Configure o SMTP em `includes/credenciais_email.php` sem versionar a senha de aplicativo.
6. Acesse `http://localhost/Emprega_JF`.

## Produção

- Atualize as credenciais de produção no arquivo protegido correspondente.
- Importe as mesmas migrações no phpMyAdmin do provedor.
- Envie os mesmos arquivos testados localmente pelo FileZilla.
- Não envie arquivos de credenciais para repositórios públicos.
- Faça backup dos arquivos e do banco antes de cada atualização.

## Contas demonstrativas

```text
Empresa
CNPJ: 99.999.999/0001-91
Senha: EmpregaJF@2026

Administrador master
E-mail: admin@empregaplusjf.local
Senha: PrefeituraJF@2026

Candidatos fictícios
Senha comum: Candidato@2026
```

As credenciais demonstrativas devem ser substituídas ou removidas antes de qualquer implantação real.

## Segurança implementada

- `password_hash()` e `password_verify()`;
- proteção CSRF;
- regeneração de ID de sessão após login;
- cookies `HttpOnly` e `SameSite=Lax`;
- consultas preparadas por PDO;
- validação de perfil e propriedade dos registros;
- separação entre administrador master e gestor;
- aprovação obrigatória de empresas;
- credenciais e diretórios sensíveis protegidos.

## Aviso

Este repositório representa um projeto acadêmico/demonstrativo. Uma implantação oficial exige validação jurídica, institucional, de segurança, acessibilidade e proteção de dados.

