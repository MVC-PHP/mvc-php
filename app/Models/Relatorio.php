<?php

namespace App\Models;

use App\Config\Database;
use InvalidArgumentException;
use PDO;

class Relatorio
{
    public function __construct(private ?PDO $conexao = null)
    {
        $this->conexao ??= Database::getConnection();
    }

    public function resultados(string $status = "todos"): array
    {
        if (!in_array($status, ["todos", "aprovados", "reprovados"], true)) {
            throw new InvalidArgumentException("Filtro de situação inválido.");
        }

        $sql = "SELECT a.nome AS aluno, d.nome AS disciplina, AVG(n.valor) AS media,
                       d.media_minima
                FROM notas n
                INNER JOIN alunos a ON a.id = n.aluno_id
                INNER JOIN disciplinas d ON d.id = n.disciplina_id
                GROUP BY a.id, a.nome, d.id, d.nome, d.media_minima";
        if ($status !== "todos") {
            $sql .= $status === "aprovados"
                ? " HAVING AVG(n.valor) >= d.media_minima"
                : " HAVING AVG(n.valor) < d.media_minima";
        }
        $sql .= " ORDER BY a.nome, d.nome";
        $consulta = $this->conexao->prepare($sql);
        $consulta->execute();

        return array_map(static function (array $linha): array {
            $media = (float) $linha["media"];
            $minima = (float) $linha["media_minima"];
            return [
                "aluno" => $linha["aluno"], "disciplina" => $linha["disciplina"],
                "media" => $media, "media_minima" => $minima,
                "situacao" => $media >= $minima ? "Aprovado" : "Reprovado",
            ];
        }, $consulta->fetchAll());
    }
}
