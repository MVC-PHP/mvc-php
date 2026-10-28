<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Models\Aluno;

function verificar(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException("FALHOU: {$mensagem}");
    }

    echo "OK: {$mensagem}\n";
}

function esperarExcecao(callable $acao, string $mensagem): void
{
    try {
        $acao();
    } catch (InvalidArgumentException) {
        echo "OK: {$mensagem}\n";
        return;
    }

    throw new RuntimeException("FALHOU: {$mensagem}");
}

$pdo = new PDO("sqlite::memory:", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec("PRAGMA foreign_keys = ON");
$pdo->exec("CREATE TABLE alunos (id INTEGER PRIMARY KEY AUTOINCREMENT, nome VARCHAR(100) NOT NULL)");
$pdo->exec("CREATE TABLE disciplinas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    media_minima DECIMAL(4,2) NOT NULL
)");
$pdo->exec("CREATE TABLE notas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aluno_id INTEGER NOT NULL REFERENCES alunos(id) ON DELETE RESTRICT,
    disciplina_id INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE RESTRICT,
    valor DECIMAL(4,2) NOT NULL
)");

$model = new Aluno($pdo);

$maria = $model->criar(["nome" => "  Maria Souza  "]);
verificar($maria["id"] === 1 && $maria["nome"] === "Maria Souza", "cria aluno com ID automático e nome sem espaços");

$joao = $model->criar(["nome" => "João Silva"]);
verificar(count($model->todos()) === 2, "lista todos os alunos");
verificar($model->todos()[0]["nome"] === "João Silva", "lista ordenada por nome");
verificar($model->buscarPorId(99) === null, "retorna null para ID inexistente");
verificar(count($model->buscarPorNome("JOAO")) === 1, "busca por nome ignora maiúsculas e acentos");

esperarExcecao(fn () => $model->criar(["nome" => "maria souza"]), "bloqueia nome repetido");
esperarExcecao(fn () => $model->criar(["nome" => "Al"]), "bloqueia nome curto");
esperarExcecao(fn () => $model->criar(["nome" => str_repeat("a", 101)]), "bloqueia nome com mais de 100 caracteres");
esperarExcecao(fn () => $model->criar([]), "bloqueia nome vazio");

$editado = $model->atualizar($maria["id"], ["nome" => "Maria Souza"]);
verificar($editado["nome"] === "Maria Souza", "atualiza mantendo o próprio nome");
esperarExcecao(fn () => $model->atualizar($maria["id"], ["nome" => "joão silva"]), "bloqueia renomear para nome de outro");
esperarExcecao(fn () => $model->atualizar(99, ["nome" => "Fulano"]), "bloqueia atualizar aluno inexistente");

$pdo->exec("INSERT INTO disciplinas (nome, media_minima) VALUES ('PHP', 6), ('Banco de Dados', 7)");
$pdo->exec("INSERT INTO notas (aluno_id, disciplina_id, valor) VALUES (1, 1, 8), (1, 1, 6), (1, 2, 5)");

verificar($model->temNotas($maria["id"]) && !$model->temNotas($joao["id"]), "detecta aluno com notas");

$disciplinas = $model->disciplinasComMedia($maria["id"]);
verificar(
    count($disciplinas) === 2
    && $disciplinas[0]["nome"] === "Banco de Dados" && $disciplinas[0]["situacao"] === "Reprovado"
    && $disciplinas[1]["nome"] === "PHP" && $disciplinas[1]["media"] === 7.0
    && $disciplinas[1]["notas"] === [8.0, 6.0] && $disciplinas[1]["situacao"] === "Aprovado",
    "lista disciplinas do aluno com notas, média e situação (JOIN)"
);

try {
    $model->excluir($maria["id"]);
    throw new RuntimeException("FALHOU: FK deveria impedir exclusão");
} catch (PDOException) {
    echo "OK: chave estrangeira impede excluir aluno com notas\n";
}

verificar($model->excluir($joao["id"]) && $model->buscarPorId($joao["id"]) === null, "exclui aluno sem notas");
verificar($model->excluir(99) === false, "excluir ID inexistente retorna false");

echo "\nTodos os testes de Aluno passaram.\n";
