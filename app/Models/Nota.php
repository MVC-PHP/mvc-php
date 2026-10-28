<?php

namespace App\Models;

use App\Config\Database;
use InvalidArgumentException;
use PDO;

class Nota
{
    private PDO $conexao;

    public function __construct(?PDO $conexao = null)
    {
        $this->conexao = $conexao ?? Database::getConnection();
    }

    public function todos(): array
    {
        $consulta = $this->conexao->query(
            "SELECT id, aluno_id, disciplina_id, valor FROM notas ORDER BY id"
        );

        return array_map([$this, "hidratar"], $consulta->fetchAll());
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, aluno_id, disciplina_id, valor FROM notas WHERE id = :id"
        );
        $consulta->execute(["id" => $id]);
        $nota = $consulta->fetch();

        return $nota === false ? null : $this->hidratar($nota);
    }

    public function criar(array $dados): array
    {
        $erros = $this->validar($dados);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $nota = $this->normalizarDados($dados);
        $consulta = $this->conexao->prepare(
            "INSERT INTO notas (aluno_id, disciplina_id, valor)
             VALUES (:aluno_id, :disciplina_id, :valor)"
        );
        $consulta->execute($nota);

        $criada = $this->buscarPorId((int) $this->conexao->lastInsertId());

        if ($criada === null) {
            throw new \RuntimeException("A nota foi inserida, mas não pode ser consultada.");
        }

        return $criada;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new InvalidArgumentException("Nota não encontrada.");
        }

        $erros = $this->validar($dados);

        if ($erros) {
            throw new InvalidArgumentException(implode(" ", $erros));
        }

        $nota = $this->normalizarDados($dados);
        $nota["id"] = $id;

        $consulta = $this->conexao->prepare(
            "UPDATE notas
             SET aluno_id = :aluno_id, disciplina_id = :disciplina_id, valor = :valor
             WHERE id = :id"
        );
        $consulta->execute($nota);

        $atualizada = $this->buscarPorId($id);

        if ($atualizada === null) {
            throw new \RuntimeException("A nota foi atualizada, mas não pode ser consultada.");
        }

        return $atualizada;
    }

    public function excluir(int $id): bool
    {
        $consulta = $this->conexao->prepare("DELETE FROM notas WHERE id = :id");
        $consulta->execute(["id" => $id]);

        return $consulta->rowCount() > 0;
    }

    public function validar(array $dados): array
    {
        $erros = [];
        $alunoId = filter_var($dados["aluno_id"] ?? null, FILTER_VALIDATE_INT);
        $disciplinaId = filter_var($dados["disciplina_id"] ?? null, FILTER_VALIDATE_INT);
        $valorTexto = str_replace(",", ".", trim((string) ($dados["valor"] ?? "")));

        if ($alunoId === false || $alunoId < 1 || !$this->registroExiste("alunos", $alunoId)) {
            $erros[] = "Selecione um aluno válido.";
        }

        if (
            $disciplinaId === false
            || $disciplinaId < 1
            || !$this->registroExiste("disciplinas", $disciplinaId)
        ) {
            $erros[] = "Selecione uma disciplina válida.";
        }

        if ($valorTexto === "" || !is_numeric($valorTexto)) {
            $erros[] = "Informe uma nota numérica.";
        } elseif ((float) $valorTexto < 0 || (float) $valorTexto > 10) {
            $erros[] = "A nota deve estar entre 0 e 10.";
        }

        return $erros;
    }

    public function porAluno(int $alunoId): array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, aluno_id, disciplina_id, valor
             FROM notas WHERE aluno_id = :aluno_id ORDER BY id"
        );
        $consulta->execute(["aluno_id" => $alunoId]);

        return array_map([$this, "hidratar"], $consulta->fetchAll());
    }

    public function porDisciplina(int $disciplinaId): array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, aluno_id, disciplina_id, valor
             FROM notas WHERE disciplina_id = :disciplina_id ORDER BY id"
        );
        $consulta->execute(["disciplina_id" => $disciplinaId]);

        return array_map([$this, "hidratar"], $consulta->fetchAll());
    }

    public function media(int $alunoId, int $disciplinaId): ?float
    {
        $consulta = $this->conexao->prepare(
            "SELECT AVG(valor) FROM notas
             WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id"
        );
        $consulta->execute([
            "aluno_id" => $alunoId,
            "disciplina_id" => $disciplinaId,
        ]);
        $media = $consulta->fetchColumn();

        return $media === null || $media === false ? null : (float) $media;
    }

    public function situacao(int $alunoId, int $disciplinaId): string
    {
        $disciplina = $this->buscarDisciplina($disciplinaId);

        if ($disciplina === null) {
            throw new InvalidArgumentException("Disciplina não encontrada.");
        }

        $media = $this->media($alunoId, $disciplinaId);

        if ($media === null) {
            return "Sem notas";
        }

        return $media >= (float) $disciplina["media_minima"]
            ? "Aprovado"
            : "Reprovado";
    }

    private function normalizarDados(array $dados): array
    {
        return [
            "aluno_id" => (int) $dados["aluno_id"],
            "disciplina_id" => (int) $dados["disciplina_id"],
            "valor" => (float) str_replace(",", ".", trim((string) $dados["valor"])),
        ];
    }

    private function registroExiste(string $tabela, int $id): bool
    {
        if (!in_array($tabela, ["alunos", "disciplinas"], true)) {
            throw new InvalidArgumentException("Tabela de relacionamento inválida.");
        }

        $consulta = $this->conexao->prepare("SELECT 1 FROM {$tabela} WHERE id = :id");
        $consulta->execute(["id" => $id]);

        return $consulta->fetchColumn() !== false;
    }

    private function buscarDisciplina(int $id): ?array
    {
        $consulta = $this->conexao->prepare(
            "SELECT id, nome, media_minima FROM disciplinas WHERE id = :id"
        );
        $consulta->execute(["id" => $id]);
        $disciplina = $consulta->fetch();

        return $disciplina === false ? null : $disciplina;
    }

    private function hidratar(array $nota): array
    {
        return [
            "id" => (int) $nota["id"],
            "aluno_id" => (int) $nota["aluno_id"],
            "disciplina_id" => (int) $nota["disciplina_id"],
            "valor" => (float) $nota["valor"],
        ];
    }
}
