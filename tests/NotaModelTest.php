<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Models\Nota;
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
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec("PRAGMA foreign_keys = ON");
$pdo->exec("CREATE TABLE alunos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL
)");
$pdo->exec("CREATE TABLE disciplinas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    media_minima DECIMAL(4,2) NOT NULL CHECK (media_minima BETWEEN 0 AND 10)
)");
$pdo->exec("CREATE TABLE notas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aluno_id INTEGER NOT NULL,
    disciplina_id INTEGER NOT NULL,
    valor DECIMAL(4,2) NOT NULL CHECK (valor BETWEEN 0 AND 10),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE RESTRICT,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE RESTRICT
)");
$pdo->exec("INSERT INTO alunos (nome) VALUES ('Aluno de teste')");
$alunoId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO disciplinas (nome, media_minima) VALUES ('PHP', 6.00)");
$disciplinaId = (int) $pdo->lastInsertId();

$model = new Nota($pdo);

$nota = $model->criar([
    "aluno_id" => $alunoId,
    "disciplina_id" => $disciplinaId,
    "valor" => "7,5",
]);
verificar($nota["id"] > 0 && $nota["valor"] === 7.5, "cadastra e normaliza uma nota");
verificar(count($model->todos()) === 1, "lista as notas");
verificar($model->buscarPorId($nota["id"])["aluno_id"] === $alunoId, "consulta por ID");

$atualizada = $model->atualizar($nota["id"], [
    "aluno_id" => $alunoId,
    "disciplina_id" => $disciplinaId,
    "valor" => 5,
]);
verificar($atualizada["valor"] === 5.0, "atualiza uma nota");

$segunda = $model->criar([
    "aluno_id" => $alunoId,
    "disciplina_id" => $disciplinaId,
    "valor" => 9,
]);
verificar($model->media($alunoId, $disciplinaId) === 7.0, "calcula a media correta");
verificar($model->situacao($alunoId, $disciplinaId) === "Aprovado", "calcula situacao de aprovacao");

$model->atualizar($segunda["id"], [
    "aluno_id" => $alunoId,
    "disciplina_id" => $disciplinaId,
    "valor" => 4,
]);
verificar($model->situacao($alunoId, $disciplinaId) === "Reprovado", "calcula situacao de reprovacao");
verificar(count($model->porAluno($alunoId)) === 2, "consulta notas por aluno");
verificar(count($model->porDisciplina($disciplinaId)) === 2, "consulta notas por disciplina");

esperarExcecao(
    fn () => $model->criar(["aluno_id" => 9999, "disciplina_id" => $disciplinaId, "valor" => 8]),
    "recusa aluno inexistente"
);
esperarExcecao(
    fn () => $model->criar(["aluno_id" => $alunoId, "disciplina_id" => 9999, "valor" => 8]),
    "recusa disciplina inexistente"
);
esperarExcecao(
    fn () => $model->criar(["aluno_id" => $alunoId, "disciplina_id" => $disciplinaId, "valor" => 11]),
    "recusa nota fora de 0 a 10"
);

try {
    $pdo->exec("DELETE FROM alunos WHERE id = {$alunoId}");
    throw new RuntimeException("FALHOU: protege a chave estrangeira de aluno");
} catch (PDOException) {
    echo "OK: protege a chave estrangeira de aluno\n";
}

try {
    $pdo->exec("DELETE FROM disciplinas WHERE id = {$disciplinaId}");
    throw new RuntimeException("FALHOU: protege a chave estrangeira de disciplina");
} catch (PDOException) {
    echo "OK: protege a chave estrangeira de disciplina\n";
}

try {
    $pdo->exec(
        "INSERT INTO notas (aluno_id, disciplina_id, valor)
         VALUES ({$alunoId}, {$disciplinaId}, 10.01)"
    );
    throw new RuntimeException("FALHOU: protege o intervalo da nota no banco");
} catch (PDOException) {
    echo "OK: protege o intervalo da nota no banco\n";
}

verificar($model->excluir($nota["id"]), "exclui uma nota existente");
verificar(!$model->excluir(9999), "informa exclusao de nota inexistente");

echo "\nTodos os testes de logica do Model Nota passaram.\n";
