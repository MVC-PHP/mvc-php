<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Models\Disciplina;

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

$model = new Disciplina($pdo);

$php = $model->criar(["nome" => "Programação PHP", "media_minima" => "6"]);
verificar($php["id"] === 1 && $php["media_minima"] === 6.0, "cria disciplina com ID automático");

$banco = $model->criar(["nome" => "Banco de Dados", "media_minima" => "7,5"]);
verificar($banco["media_minima"] === 7.5, "aceita média com vírgula");

verificar(count($model->todos()) === 2, "lista todas as disciplinas");
verificar($model->buscarPorId(99) === null, "retorna null para ID inexistente");
verificar(count($model->buscarPorNome("PROGRAMACAO")) === 1, "busca por nome ignora maiúsculas e acentos");

esperarExcecao(fn () => $model->criar(["nome" => "programacao php", "media_minima" => "5"]), "bloqueia nome repetido");
esperarExcecao(fn () => $model->criar(["nome" => "AB", "media_minima" => "5"]), "bloqueia nome curto");
esperarExcecao(fn () => $model->criar(["nome" => "Redes", "media_minima" => "11"]), "bloqueia média fora de 0 a 10");

$editada = $model->atualizar($php["id"], ["nome" => "Programação PHP", "media_minima" => "5"]);
verificar($editada["media_minima"] === 5.0, "atualiza mantendo o próprio nome");
esperarExcecao(fn () => $model->atualizar($php["id"], ["nome" => "Banco de dados", "media_minima" => "5"]), "bloqueia renomear para nome de outra");

$pdo->exec("INSERT INTO alunos (nome) VALUES ('Ana'), ('Bruno')");
$pdo->exec("INSERT INTO notas (aluno_id, disciplina_id, valor) VALUES (1, 1, 8), (1, 1, 6), (2, 1, 3)");

verificar($model->temNotas($php["id"]) && !$model->temNotas($banco["id"]), "detecta disciplina com notas");

$alunos = $model->alunosComMedia($php["id"]);
verificar(
    count($alunos) === 2
    && $alunos[0]["nome"] === "Ana" && $alunos[0]["media"] === 7.0 && $alunos[0]["situacao"] === "Aprovado"
    && $alunos[1]["situacao"] === "Reprovado",
    "lista alunos da disciplina com média e situação (JOIN)"
);

try {
    $model->excluir($php["id"]);
    throw new RuntimeException("FALHOU: FK deveria impedir exclusão");
} catch (PDOException) {
    echo "OK: chave estrangeira impede excluir disciplina com notas\n";
}

verificar($model->excluir($banco["id"]) && $model->buscarPorId($banco["id"]) === null, "exclui disciplina sem notas");
verificar($model->excluir(99) === false, "excluir ID inexistente retorna false");

echo "\nTodos os testes de Disciplina passaram.\n";
