USE sistema_academico;

-- Usuários de teste: admin@escola.com / admin123 e usuario@escola.com / usuario123
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
    ('Administrador', 'admin@escola.com', '$2y$10$CPqr2P2dbXAcDcpwyYl7ROvWoTIMsEo7Q0sc2LtqsNjeGEFarGmq6', 'admin'),
    ('Usuário Comum', 'usuario@escola.com', '$2y$10$1DPC4oDCNBaxKChQkgcE5urdLtnhN9EGjKvZyh6xhc0r2HksDpBB.', 'usuario');

INSERT INTO alunos (id, nome) VALUES
    (1, 'Ana Souza'),
    (2, 'Bruno Lima'),
    (3, 'Carla Mendes'),
    (4, 'Diego Ferreira'),
    (5, 'João Pereira');

INSERT INTO disciplinas (id, nome, media_minima) VALUES
    (1, 'Linguagem de Programação PHP', 6.00),
    (2, 'Banco de Dados', 7.00),
    (3, 'Engenharia de Software', 6.00);

INSERT INTO notas (aluno_id, disciplina_id, valor) VALUES
    (1, 1, 9.00), (1, 1, 8.50), (1, 2, 7.50),
    (2, 1, 5.00), (2, 1, 6.50), (2, 3, 8.00),
    (3, 2, 4.00), (3, 2, 5.50), (3, 3, 9.50),
    (4, 1, 7.00), (4, 3, 6.00);
-- João Pereira (id 5) fica sem notas: serve para demonstrar a exclusão permitida.
