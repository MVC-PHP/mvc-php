<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

use App\Controllers\NotaController;
use App\Controllers\RelatorioController;
use App\Models\Relatorio;

define("BASE_URL", "/mvc-php");
session_start();
ob_start();

function conferir(bool $ok, string $nome): void
{
    if (!$ok) throw new RuntimeException("Falhou: " . $nome);
    echo "OK: {$nome}\n";
}

$pdo = new PDO("sqlite::memory:", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec("CREATE TABLE alunos (id INTEGER PRIMARY KEY, nome TEXT)");
$pdo->exec("CREATE TABLE disciplinas (id INTEGER PRIMARY KEY, nome TEXT, media_minima DECIMAL(4,2))");
$pdo->exec("CREATE TABLE notas (id INTEGER PRIMARY KEY, aluno_id INTEGER, disciplina_id INTEGER, valor DECIMAL(4,2))");
$modelo = new Relatorio($pdo);
conferir($modelo->resultados() === [], "relatório vazio");
$pdo->exec("INSERT INTO alunos VALUES (1, 'Ana'), (2, '=SOMA(1;1)')");
$pdo->exec("INSERT INTO disciplinas VALUES (1, 'Programação', 6), (2, 'História', 7)");
$pdo->exec("INSERT INTO notas VALUES (1,1,1,8),(2,1,1,4),(3,2,2,3)");
$todos = $modelo->resultados();
conferir(count($todos) === 2 && $todos[1]["media"] === 6.0 && $todos[1]["situacao"] === "Aprovado", "múltiplas notas e igualdade à média mínima");
conferir(count($modelo->resultados("aprovados")) === 1, "filtro aprovados");
conferir(count($modelo->resultados("reprovados")) === 1, "filtro reprovados");
try { $modelo->resultados("outro"); throw new RuntimeException("Filtro inválido aceito"); }
catch (InvalidArgumentException) { echo "OK: filtro inválido rejeitado\n"; }

$_GET["status"] = "todos";
$controller = new RelatorioController($pdo);
$html = $controller->index();
conferir(str_contains($html, "Relatório de Desempenho") && str_contains($html, "Baixar CSV"), "página do relatório");
ob_start();
$controller->exportar();
$csv = ob_get_clean();
conferir(str_starts_with($csv, "\xEF\xBB\xBF"), "CSV UTF-8 com BOM");
$arquivo = fopen("php://memory", "w+");
fwrite($arquivo, substr($csv, 3)); rewind($arquivo);
$cabecalho = fgetcsv($arquivo, 0, ";", '"', "");
$linha1 = fgetcsv($arquivo, 0, ";", '"', "");
$linha2 = fgetcsv($arquivo, 0, ";", '"', "");
conferir($cabecalho === ["Aluno", "Disciplina", "Média", "Média mínima", "Situação"], "colunas e separador CSV");
conferir($linha2[1] === "Programação" && $linha2[2] === "6,00" && $linha1[0] === "'=SOMA(1;1)", "acentos, médias e proteção CSV");
conferir(fgetcsv($arquivo, 0, ";", '"', "") === false, "quantidade de linhas CSV");
fclose($arquivo);
$_GET["status"] = "reprovados";
ob_start(); $controller->exportar(); $filtrado = ob_get_clean();
conferir(substr_count($filtrado, "\n") === 2, "filtro CSV");
$_GET["status"] = "invalido";
conferir(str_contains($controller->index(), "Ana"), "filtro inválido usa todos");

$api = new NotaController($pdo);
unset($_GET["id"]);
conferir(count(json_decode($api->list(), true)) === 3, "API GET listagem");
$_GET["id"] = "1";
conferir(json_decode($api->list(), true)["valor"] == 8.0, "API GET detalhe");
$_GET["id"] = "99";
$api->list(); conferir(http_response_code() === 404, "API GET inexistente");
$api->update(["aluno_id" => 1, "disciplina_id" => 1, "valor" => 5]);
conferir(http_response_code() === 404, "API PUT inexistente");
$_GET["id"] = "1";
conferir(json_decode($api->update(["aluno_id" => 1, "disciplina_id" => 1, "valor" => 7]), true)["valor"] == 7.0, "API PUT válido");
$api->update(["aluno_id" => 1, "disciplina_id" => 1, "valor" => 11]);
conferir(http_response_code() === 422, "API PUT inválido");
ob_end_flush();
