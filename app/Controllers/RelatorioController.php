<?php

namespace App\Controllers;

use App\Models\Relatorio;
use App\Views\Render;
use InvalidArgumentException;
use PDO;

class RelatorioController
{
    private Relatorio $model;

    public function __construct(?PDO $conexao = null)
    {
        $this->model = new Relatorio($conexao);
    }

    private function status(): string
    {
        $status = $_GET["status"] ?? "todos";
        return is_string($status) && in_array($status, ["todos", "aprovados", "reprovados"], true)
            ? $status : "todos";
    }

    public function index(): string
    {
        $status = $this->status();
        return (new Render())->render("relatorios/index", [
            "titulo" => "Relatório de Desempenho",
            "resultados" => $this->model->resultados($status),
            "status" => $status,
        ]);
    }

    public function exportar(): void
    {
        $resultados = $this->model->resultados($this->status());
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename="relatorio_desempenho.csv"');
        $saida = fopen("php://output", "wb");
        fwrite($saida, "\xEF\xBB\xBF");
        fputcsv($saida, ["Aluno", "Disciplina", "Média", "Média mínima", "Situação"], ";", '"', "");
        foreach ($resultados as $linha) {
            fputcsv($saida, [
                self::textoCsv($linha["aluno"]), self::textoCsv($linha["disciplina"]),
                number_format($linha["media"], 2, ",", ""),
                number_format($linha["media_minima"], 2, ",", ""),
                $linha["situacao"],
            ], ";", '"', "");
        }
        fclose($saida);
    }

    private static function textoCsv(string $valor): string
    {
        return preg_match('/^\s*[=+\-@\t\r\n]/u', $valor) ? "'" . $valor : $valor;
    }
}
