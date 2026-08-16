-- Emprega+ Juiz de Fora
-- Sistema Municipal de Banco de Curriculos e Empregabilidade
-- Estrutura inicial do banco de dados

SET NAMES utf8mb4;
SET time_zone = '-03:00';

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('candidato', 'empresa', 'administrador') NOT NULL,
    status ENUM('pendente', 'ativo', 'inativo', 'bloqueado') NOT NULL DEFAULT 'pendente',
    ultimo_acesso DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_email (email),
    KEY idx_usuarios_perfil_status (perfil, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cidades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    uf CHAR(2) NOT NULL,
    codigo_ibge VARCHAR(7) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cidades_nome_uf (nome, uf),
    UNIQUE KEY uk_cidades_codigo_ibge (codigo_ibge),
    KEY idx_cidades_uf (uf)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS areas_profissionais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_areas_profissionais_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidatos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    cidade_id BIGINT UNSIGNED NOT NULL,
    cpf CHAR(11) NOT NULL,
    nome_completo VARCHAR(180) NOT NULL,
    data_nascimento DATE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cep CHAR(8) NULL,
    logradouro VARCHAR(180) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(120) NULL,
    consentimento_dados_em DATETIME NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_candidatos_usuario (usuario_id),
    UNIQUE KEY uk_candidatos_cpf (cpf),
    KEY idx_candidatos_cidade (cidade_id),
    KEY idx_candidatos_bairro (bairro),
    CONSTRAINT fk_candidatos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_candidatos_cidade
        FOREIGN KEY (cidade_id) REFERENCES cidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS empresas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    cidade_id BIGINT UNSIGNED NOT NULL,
    cnpj CHAR(14) NOT NULL,
    razao_social VARCHAR(180) NOT NULL,
    nome_fantasia VARCHAR(180) NULL,
    telefone VARCHAR(20) NOT NULL,
    cep CHAR(8) NULL,
    logradouro VARCHAR(180) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(120) NULL,
    responsavel_nome VARCHAR(150) NOT NULL,
    aprovada_em DATETIME NULL,
    aprovada_por BIGINT UNSIGNED NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_empresas_usuario (usuario_id),
    UNIQUE KEY uk_empresas_cnpj (cnpj),
    KEY idx_empresas_cidade (cidade_id),
    KEY idx_empresas_aprovada_por (aprovada_por),
    CONSTRAINT fk_empresas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_empresas_cidade
        FOREIGN KEY (cidade_id) REFERENCES cidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_empresas_aprovada_por
        FOREIGN KEY (aprovada_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS curriculos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidato_id BIGINT UNSIGNED NOT NULL,
    area_profissional_id BIGINT UNSIGNED NULL,
    titulo_profissional VARCHAR(150) NOT NULL,
    objetivo_profissional TEXT NULL,
    resumo_profissional TEXT NULL,
    disponibilidade VARCHAR(120) NULL,
    pretensao_salarial DECIMAL(10,2) NULL,
    visivel TINYINT(1) NOT NULL DEFAULT 1,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_curriculos_candidato (candidato_id),
    KEY idx_curriculos_area_visivel (area_profissional_id, visivel),
    CONSTRAINT fk_curriculos_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_curriculos_area
        FOREIGN KEY (area_profissional_id) REFERENCES areas_profissionais(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS experiencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidato_id BIGINT UNSIGNED NOT NULL,
    empresa VARCHAR(180) NOT NULL,
    cargo VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    emprego_atual TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_experiencias_candidato (candidato_id),
    CONSTRAINT fk_experiencias_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS formacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidato_id BIGINT UNSIGNED NOT NULL,
    nivel ENUM(
        'fundamental_incompleto', 'fundamental_completo',
        'medio_incompleto', 'medio_completo',
        'tecnico', 'superior_incompleto', 'superior_completo',
        'pos_graduacao', 'mestrado', 'doutorado'
    ) NOT NULL,
    instituicao VARCHAR(180) NOT NULL,
    curso VARCHAR(180) NULL,
    data_inicio DATE NULL,
    data_conclusao DATE NULL,
    cursando TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_formacoes_candidato (candidato_id),
    KEY idx_formacoes_nivel (nivel),
    CONSTRAINT fk_formacoes_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cursos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidato_id BIGINT UNSIGNED NOT NULL,
    nome VARCHAR(180) NOT NULL,
    instituicao VARCHAR(180) NULL,
    carga_horaria SMALLINT UNSIGNED NULL,
    ano_conclusao YEAR NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cursos_candidato (candidato_id),
    CONSTRAINT fk_cursos_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS competencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_competencias_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidato_competencias (
    candidato_id BIGINT UNSIGNED NOT NULL,
    competencia_id BIGINT UNSIGNED NOT NULL,
    nivel ENUM('basico', 'intermediario', 'avancado') NULL,
    PRIMARY KEY (candidato_id, competencia_id),
    KEY idx_candidato_competencias_competencia (competencia_id),
    CONSTRAINT fk_candidato_competencias_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_candidato_competencias_competencia
        FOREIGN KEY (competencia_id) REFERENCES competencias(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS idiomas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_idiomas_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidato_idiomas (
    candidato_id BIGINT UNSIGNED NOT NULL,
    idioma_id BIGINT UNSIGNED NOT NULL,
    nivel ENUM('basico', 'intermediario', 'avancado', 'fluente', 'nativo') NOT NULL,
    PRIMARY KEY (candidato_id, idioma_id),
    KEY idx_candidato_idiomas_idioma (idioma_id),
    CONSTRAINT fk_candidato_idiomas_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_candidato_idiomas_idioma
        FOREIGN KEY (idioma_id) REFERENCES idiomas(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vagas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    cidade_id BIGINT UNSIGNED NOT NULL,
    area_profissional_id BIGINT UNSIGNED NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NOT NULL,
    requisitos TEXT NULL,
    tipo_contrato ENUM('clt', 'temporario', 'estagio', 'aprendiz', 'autonomo', 'pj', 'outro') NOT NULL,
    modalidade ENUM('presencial', 'hibrido', 'remoto') NOT NULL DEFAULT 'presencial',
    salario_minimo DECIMAL(10,2) NULL,
    salario_maximo DECIMAL(10,2) NULL,
    quantidade_vagas SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('rascunho', 'publicada', 'pausada', 'encerrada') NOT NULL DEFAULT 'rascunho',
    publicada_em DATETIME NULL,
    encerra_em DATE NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_vagas_empresa_status (empresa_id, status),
    KEY idx_vagas_cidade_status (cidade_id, status),
    KEY idx_vagas_area_status (area_profissional_id, status),
    CONSTRAINT fk_vagas_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_vagas_cidade
        FOREIGN KEY (cidade_id) REFERENCES cidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_vagas_area
        FOREIGN KEY (area_profissional_id) REFERENCES areas_profissionais(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidaturas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vaga_id BIGINT UNSIGNED NOT NULL,
    candidato_id BIGINT UNSIGNED NOT NULL,
    status ENUM('enviada', 'em_analise', 'entrevista', 'aprovada', 'reprovada', 'contratado', 'cancelada') NOT NULL DEFAULT 'enviada',
    mensagem TEXT NULL,
    candidatou_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_candidaturas_vaga_candidato (vaga_id, candidato_id),
    KEY idx_candidaturas_candidato_status (candidato_id, status),
    KEY idx_candidaturas_vaga_status (vaga_id, status),
    CONSTRAINT fk_candidaturas_vaga
        FOREIGN KEY (vaga_id) REFERENCES vagas(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_candidaturas_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recuperacoes_senha (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expira_em DATETIME NOT NULL,
    utilizado_em DATETIME NULL,
    solicitado_ip VARCHAR(45) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_recuperacoes_senha_token (token_hash),
    KEY idx_recuperacoes_usuario_criado (usuario_id, criado_em),
    KEY idx_recuperacoes_validade (expira_em, utilizado_em),
    CONSTRAINT fk_recuperacoes_senha_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
