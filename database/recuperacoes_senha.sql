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

