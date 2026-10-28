-- Selecione ou crie o banco "sistema_academico" no phpMyAdmin antes de importar.
-- O script nao remove tabelas nem bancos existentes.

CREATE TABLE IF NOT EXISTS alunos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS disciplinas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    media_minima DECIMAL(4,2) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT chk_disciplinas_media_minima
        CHECK (media_minima BETWEEN 0.00 AND 10.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    aluno_id INT UNSIGNED NOT NULL,
    disciplina_id INT UNSIGNED NOT NULL,
    valor DECIMAL(4,2) NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_notas_aluno_id (aluno_id),
    INDEX idx_notas_disciplina_id (disciplina_id),
    CONSTRAINT chk_notas_valor CHECK (valor BETWEEN 0.00 AND 10.00),
    CONSTRAINT fk_notas_aluno
        FOREIGN KEY (aluno_id) REFERENCES alunos (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_notas_disciplina
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
