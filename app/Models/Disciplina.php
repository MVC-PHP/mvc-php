<?php

namespace App\Models;

use App\Config\Database;
use InvalidArgumentException;
use PDO;

class Disciplina
{
    private PDO $conexao;

    public function __construct(?PDO $conexao = null)
    {
        $this->conexao = $conexao ?? Database::getConnection();
    }

    public function todos(): array
    {
        $consulta = $this->conexao->query(
            "SELECT id, nome, media_minima FROM disciplinas ORDER BY nome"
        );

        return array_map([$this, "hidratar"], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, nome, media_minima FROM disciplinas WHERE id = :id"
        );
        $consulta->execute(["id" => $id]);
        $disciplina = $consulta->fetch();

        return $disciplina === false ? null : $this->hidratar($disciplina);
    }

    public function buscarPorNome(string $termo): array
    {
        $termo = $this->simplificar(trim($termo));

        return array_values(array_filter(
            $this->todos(),
            fn (array $disciplina): bool => str_contains($this->simplificar($disciplina["nome"]), $termo)
        ));
    }

    public function criar(array $dados): array
    {
        $erros = $this->validar($dados);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $consulta = $this->conexao->prepare(
            "INSERT INTO disciplinas (nome, media_minima) VALUES (:nome, :media_minima)"
        );
        $consulta->execute($this->normalizarDados($dados));

        $criada = $this->buscarPorId((int) $this->conexao->lastInsertId());

        if ($criada === null) {
            throw new \RuntimeException("A disciplina foi inserida, mas não pode ser consultada.");
        }

        return $criada;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new InvalidArgumentException("Disciplina não encontrada.");
        }

        $erros = $this->validar($dados, $id);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $disciplina = $this->normalizarDados($dados);
        $disciplina["id"] = $id;

        $consulta = $this->conexao->prepare(
            "UPDATE disciplinas SET nome = :nome, media_minima = :media_minima WHERE id = :id"
        );
        $consulta->execute($disciplina);

        return $this->buscarPorId($id);
    }

    public function excluir(int $id): bool
    {
        $consulta = $this->conexao->prepare("DELETE FROM disciplinas WHERE id = :id");
        $consulta->execute(["id" => $id]);

        return $consulta->rowCount() > 0;
    }

    public function temNotas(int $id): bool
    {
        $consulta = $this->conexao->prepare(
            "SELECT 1 FROM notas WHERE disciplina_id = :id LIMIT 1"
        );
        $consulta->execute(["id" => $id]);

        return $consulta->fetchColumn() !== false;
    }

    // Relacionamento: alunos que têm notas na disciplina, com média calculada no banco.
    public function alunosComMedia(int $id): array
    {
        $consulta = $this->conexao->prepare(
            "SELECT a.id, a.nome, AVG(n.valor) AS media, d.media_minima
             FROM notas n
             INNER JOIN alunos a ON a.id = n.aluno_id
             INNER JOIN disciplinas d ON d.id = n.disciplina_id
             WHERE n.disciplina_id = :id
             GROUP BY a.id, a.nome, d.media_minima
             ORDER BY a.nome"
        );
        $consulta->execute(["id" => $id]);

        return array_map(function (array $linha): array {
            $media = (float) $linha["media"];

            return [
                "id" => (int) $linha["id"],
                "nome" => $linha["nome"],
                "media" => $media,
                "situacao" => $media >= (float) $linha["media_minima"] ? "Aprovado" : "Reprovado",
            ];
        }, $consulta->fetchAll());
    }

    public function validar(array $dados, ?int $ignorarId = null): array
    {
        $erros = [];

        $nome = trim((string) ($dados["nome"] ?? ""));
        $mediaTexto = str_replace(",", ".", trim((string) ($dados["media_minima"] ?? "")));

        if ($nome === "") {
            $erros[] = "Informe o nome da disciplina.";
        } elseif (mb_strlen($nome) < 3) {
            $erros[] = "O nome deve ter pelo menos 3 caracteres.";
        } elseif (mb_strlen($nome) > 100) {
            $erros[] = "O nome deve ter no máximo 100 caracteres.";
        } elseif ($this->nomeJaExiste($nome, $ignorarId)) {
            $erros[] = "Já existe uma disciplina com esse nome.";
        }

        if ($mediaTexto === "" || !is_numeric($mediaTexto)) {
            $erros[] = "Informe uma média mínima numérica.";
        } elseif ((float) $mediaTexto < 0 || (float) $mediaTexto > 10) {
            $erros[] = "A média mínima deve estar entre 0 e 10.";
        }

        return $erros;
    }

    private function normalizarDados(array $dados): array
    {
        return [
            "nome" => trim((string) $dados["nome"]),
            "media_minima" => (float) str_replace(",", ".", trim((string) $dados["media_minima"])),
        ];
    }

    private function nomeJaExiste(string $nome, ?int $ignorarId): bool
    {
        $consulta = $this->conexao->prepare(
            "SELECT nome FROM disciplinas WHERE id <> :id"
        );
        $consulta->execute(["id" => $ignorarId ?? 0]);

        foreach ($consulta->fetchAll(PDO::FETCH_COLUMN) as $existente) {
            if ($this->simplificar($existente) === $this->simplificar($nome)) {
                return true;
            }
        }

        return false;
    }

    private function hidratar(array $disciplina): array
    {
        return [
            "id" => (int) $disciplina["id"],
            "nome" => $disciplina["nome"],
            "media_minima" => (float) $disciplina["media_minima"],
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
