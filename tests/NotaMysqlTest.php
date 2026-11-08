<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Config\Database;
use App\Models\Nota;

if (getenv("RUN_DB_TESTS") !== "1") {
    fwrite(STDERR, "Defina RUN_DB_TESTS=1 para confirmar o uso do banco configurado.\n");
    exit(2);
}

function verificarMysql(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException("FALHOU: {$mensagem}");
    }

    echo "OK: {$mensagem}\n";
}

function esperarValidacaoMysql(callable $acao, string $mensagem): void
{
    try {
        $acao();
    } catch (InvalidArgumentException) {
        echo "OK: {$mensagem}\n";
        return;
    }

    throw new RuntimeException("FALHOU: {$mensagem}");
}

$pdo = Database::getConnection();
verificarMysql($pdo === Database::getConnection(), "reutiliza a conexao durante a requisicao");
verificarMysql(
    $pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION,
    "configura PDO para excecoes"
);
verificarMysql(
    $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC,
    "configura fetch associativo"
);
$pdo->beginTransaction();

try {
    $sufixo = bin2hex(random_bytes(5));
    $consulta = $pdo->prepare("INSERT INTO alunos (nome) VALUES (:nome)");
    $consulta->execute(["nome" => "Aluno teste {$sufixo}"]);
    $alunoId = (int) $pdo->lastInsertId();

    $consulta = $pdo->prepare(
        "INSERT INTO disciplinas (nome, media_minima) VALUES (:nome, :media_minima)"
    );
    $consulta->execute(["nome" => "Disciplina teste {$sufixo}", "media_minima" => 6]);
    $disciplinaId = (int) $pdo->lastInsertId();

    $model = new Nota($pdo);
    $primeira = $model->criar([
        "aluno_id" => $alunoId,
        "disciplina_id" => $disciplinaId,
        "valor" => 8,
    ]);
    verificarMysql($model->buscarPorId($primeira["id"]) !== null, "CRUD: cadastro e consulta");

    $model->atualizar($primeira["id"], [
        "aluno_id" => $alunoId,
        "disciplina_id" => $disciplinaId,
        "valor" => 4,
    ]);
    $segunda = $model->criar([
        "aluno_id" => $alunoId,
        "disciplina_id" => $disciplinaId,
        "valor" => 10,
    ]);
    verificarMysql($model->media($alunoId, $disciplinaId) === 7.0, "media no MySQL");
    verificarMysql($model->situacao($alunoId, $disciplinaId) === "Aprovado", "situacao no MySQL");

    esperarValidacaoMysql(
        fn () => $model->criar(["aluno_id" => 2147483647, "disciplina_id" => $disciplinaId, "valor" => 5]),
        "recusa aluno inexistente"
    );
    esperarValidacaoMysql(
        fn () => $model->criar(["aluno_id" => $alunoId, "disciplina_id" => 2147483647, "valor" => 5]),
        "recusa disciplina inexistente"
    );
    esperarValidacaoMysql(
        fn () => $model->criar(["aluno_id" => $alunoId, "disciplina_id" => $disciplinaId, "valor" => 10.01]),
        "recusa nota fora do intervalo"
    );

    try {
        $pdo->exec("DELETE FROM alunos WHERE id = {$alunoId}");
        throw new RuntimeException("FALHOU: integridade referencial");
    } catch (PDOException) {
        echo "OK: integridade referencial impede excluir aluno com nota\n";
    }

    try {
        $pdo->exec("DELETE FROM disciplinas WHERE id = {$disciplinaId}");
        throw new RuntimeException("FALHOU: integridade referencial da disciplina");
    } catch (PDOException) {
        echo "OK: integridade referencial impede excluir disciplina com nota\n";
    }

    try {
        $pdo->exec(
            "INSERT INTO notas (aluno_id, disciplina_id, valor)
             VALUES ({$alunoId}, {$disciplinaId}, 10.01)"
        );
        throw new RuntimeException("FALHOU: restricao CHECK da nota");
    } catch (PDOException) {
        echo "OK: restricao CHECK impede nota fora do intervalo\n";
    }

    verificarMysql($model->excluir($primeira["id"]), "CRUD: exclusao");
    verificarMysql($model->buscarPorId($primeira["id"]) === null, "registro excluido nao e encontrado");
    verificarMysql($model->excluir($segunda["id"]), "remove segunda nota de teste");

    echo "\nTodos os testes de integracao MySQL passaram.\n";
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
