<?php

namespace App\Models;

use App\Config\Database;
use InvalidArgumentException;
use PDO;

class Aluno
{
    private PDO $conexao;

    public function __construct(?PDO $conexao = null)
    {
        $this->conexao = $conexao ?? Database::getConnection();
    }

    public function todos(): array
    {
        $consulta = $this->conexao->query(
            "SELECT id, nome FROM alunos ORDER BY nome"
        );

        return array_map([$this, "hidratar"], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, nome FROM alunos WHERE id = :id"
        );
        $consulta->execute(["id" => $id]);
        $aluno = $consulta->fetch();

        return $aluno === false ? null : $this->hidratar($aluno);
    }

    public function buscarPorNome(string $termo): array
    {
        $termo = $this->simplificar(trim($termo));

        return array_values(array_filter(
            $this->todos(),
            fn (array $aluno): bool => str_contains($this->simplificar($aluno["nome"]), $termo)
        ));
    }

    public function criar(array $dados): array
    {
        $erros = $this->validar($dados);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $consulta = $this->conexao->prepare(
            "INSERT INTO alunos (nome) VALUES (:nome)"
        );
        $consulta->execute($this->normalizarDados($dados));

        $criado = $this->buscarPorId((int) $this->conexao->lastInsertId());

        if ($criado === null) {
            throw new \RuntimeException("O aluno foi inserido, mas não pode ser consultado.");
        }

        return $criado;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new InvalidArgumentException("Aluno não encontrado.");
        }

        $erros = $this->validar($dados, $id);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $aluno = $this->normalizarDados($dados);
        $aluno["id"] = $id;

        $consulta = $this->conexao->prepare(
            "UPDATE alunos SET nome = :nome WHERE id = :id"
        );
        $consulta->execute($aluno);

        return $this->buscarPorId($id);
    }

    public function excluir(int $id): bool
    {
        $consulta = $this->conexao->prepare("DELETE FROM alunos WHERE id = :id");
        $consulta->execute(["id" => $id]);

        return $consulta->rowCount() > 0;
    }

    public function temNotas(int $id): bool
    {
        $consulta = $this->conexao->prepare(
            "SELECT 1 FROM notas WHERE aluno_id = :id LIMIT 1"
        );
        $consulta->execute(["id" => $id]);

        return $consulta->fetchColumn() !== false;
    }

    public function disciplinasComMedia(int $id): array
    {
        $consulta = $this->conexao->prepare(
            "SELECT disciplina_id, valor FROM notas WHERE aluno_id = :id ORDER BY id"
        );
        $consulta->execute(["id" => $id]);
        $notas = [];

        foreach ($consulta->fetchAll() as $nota) {
            $notas[(int) $nota["disciplina_id"]][] = (float) $nota["valor"];
        }

        $consulta = $this->conexao->prepare(
            "SELECT d.id, d.nome, d.media_minima, AVG(n.valor) AS media
             FROM notas n
             INNER JOIN disciplinas d ON d.id = n.disciplina_id
             WHERE n.aluno_id = :id
             GROUP BY d.id, d.nome, d.media_minima
             ORDER BY d.nome"
        );
        $consulta->execute(["id" => $id]);

        return array_map(function (array $linha) use ($notas): array {
            $media = (float) $linha["media"];

            return [
                "id" => (int) $linha["id"],
                "nome" => $linha["nome"],
                "media_minima" => (float) $linha["media_minima"],
                "notas" => $notas[(int) $linha["id"]] ?? [],
                "media" => $media,
                "situacao" => $media >= (float) $linha["media_minima"] ? "Aprovado" : "Reprovado",
            ];
        }, $consulta->fetchAll());
    }

    public function validar(array $dados, ?int $ignorarId = null): array
    {
        $erros = [];
        $nome = trim((string) ($dados["nome"] ?? ""));

        if ($nome === "") {
            $erros[] = "Informe o nome do aluno.";
        } elseif (mb_strlen($nome) < 3) {
            $erros[] = "O nome deve ter pelo menos 3 caracteres.";
        } elseif (mb_strlen($nome) > 100) {
            $erros[] = "O nome deve ter no máximo 100 caracteres.";
        } elseif ($this->nomeJaExiste($nome, $ignorarId)) {
            $erros[] = "Já existe um aluno com esse nome.";
        }

        return $erros;
    }

    private function normalizarDados(array $dados): array
    {
        return [
            "nome" => trim((string) $dados["nome"]),
        ];
    }

    private function nomeJaExiste(string $nome, ?int $ignorarId): bool
    {
        $consulta = $this->conexao->prepare(
            "SELECT nome FROM alunos WHERE id <> :id"
        );
        $consulta->execute(["id" => $ignorarId ?? 0]);

        foreach ($consulta->fetchAll(PDO::FETCH_COLUMN) as $existente) {
            if ($this->simplificar($existente) === $this->simplificar($nome)) {
                return true;
            }
        }

        return false;
    }

    private function hidratar(array $aluno): array
    {
        return [
            "id" => (int) $aluno["id"],
            "nome" => $aluno["nome"],
        ];
    }

    private function simplificar(string $texto): string
    {
        return strtr(mb_strtolower($texto, "UTF-8"), [
            "á" => "a", "à" => "a", "â" => "a", "ã" => "a", "ä" => "a",
            "é" => "e", "è" => "e", "ê" => "e", "ë" => "e",
            "í" => "i", "ì" => "i", "î" => "i", "ï" => "i",
            "ó" => "o", "ò" => "o", "ô" => "o", "õ" => "o", "ö" => "o",
            "ú" => "u", "ù" => "u", "û" => "u", "ü" => "u",
            "ç" => "c", "ñ" => "n",
        ]);
    }
}
