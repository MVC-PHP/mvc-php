<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Controllers\NotaController;

define("BASE_URL", "/mvc-php");
session_start();

function verificarController(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException("FALHOU: {$mensagem}");
    }

    echo "OK: {$mensagem}\n";
}

$pdo = new PDO("sqlite::memory:", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec("PRAGMA foreign_keys = ON");
$pdo->exec("CREATE TABLE alunos (id INTEGER PRIMARY KEY, nome VARCHAR(100) NOT NULL)");
$pdo->exec(
    "CREATE TABLE disciplinas (
        id INTEGER PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        media_minima DECIMAL(4,2) NOT NULL
    )"
);
$pdo->exec(
    "CREATE TABLE notas (
        id INTEGER PRIMARY KEY,
        aluno_id INTEGER NOT NULL,
        disciplina_id INTEGER NOT NULL,
        valor DECIMAL(4,2) NOT NULL,
        FOREIGN KEY (aluno_id) REFERENCES alunos(id),
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id)
    )"
);
$pdo->exec("INSERT INTO alunos (id, nome) VALUES (1, 'Maria Teste')");
$pdo->exec("INSERT INTO disciplinas (id, nome, media_minima) VALUES (1, 'PHP Teste', 6)");
$pdo->exec("INSERT INTO notas (id, aluno_id, disciplina_id, valor) VALUES (1, 1, 1, 8.5)");

$controller = new NotaController($pdo);
$lista = $controller->index();
verificarController(str_contains($lista, "Maria Teste"), "listagem associa aluno vindo do banco");
verificarController(str_contains($lista, "PHP Teste"), "listagem associa disciplina vinda do banco");

$cadastro = $controller->criar();
verificarController(str_contains($cadastro, "Maria Teste"), "formulario de cadastro lista alunos do banco");
verificarController(str_contains($cadastro, "PHP Teste"), "formulario de cadastro lista disciplinas do banco");

$_GET["id"] = 1;
$edicao = $controller->editar();
verificarController(
    preg_match('/value="1"\s+selected/', $edicao) === 1,
    "formulario de edicao preserva relacionamentos"
);

echo "\nTodos os testes do NotaController passaram.\n";
