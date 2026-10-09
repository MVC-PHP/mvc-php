<?php

namespace App\Models;

class Nota
{
    public function todos(): array
    {
        return array_values($_SESSION["notas"] ?? []);
    }

    public function buscarPorId(int $id): ?array
    {
        foreach ($this->todos() as $nota) {
            if ((int) $nota["id"] === $id) {
                return $nota;
            }
        }

        return null;
    }

    public function criar(array $dados): array
    {
        $erros = $this->validar($dados);

        if ($erros) {
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $_SESSION["notas"] ??= [];

        $nota = $this->normalizarDados($dados);
        $nota["id"] = $this->proximoId();

        $_SESSION["notas"][] = $nota;

        return $nota;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new \InvalidArgumentException("Nota não encontrada.");
        }

        $erros = $this->validar($dados);

        if ($erros) {
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $notaAtualizada = $this->normalizarDados($dados);
        $notaAtualizada["id"] = $id;

        foreach ($_SESSION["notas"] as $indice => $nota) {
            if ((int) $nota["id"] === $id) {
                $_SESSION["notas"][$indice] = $notaAtualizada;
                break;
            }
        }

        return $notaAtualizada;
    }

    public function excluir(int $id): bool
    {
        $quantidadeAnterior = count($this->todos());

        $_SESSION["notas"] = array_values(array_filter(
            $this->todos(),
            fn (array $nota): bool => (int) $nota["id"] !== $id
        ));

        return count($_SESSION["notas"]) < $quantidadeAnterior;
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
        return array_values(array_filter(
            $this->todos(),
            fn (array $nota): bool => (int) $nota["aluno_id"] === $alunoId
        ));
    }

    public function porDisciplina(int $disciplinaId): array
    {
        return array_values(array_filter(
            $this->todos(),
            fn (array $nota): bool => (int) $nota["disciplina_id"] === $disciplinaId
        ));
    }

    public function media(int $alunoId, int $disciplinaId): ?float
    {
        $notas = array_filter(
            $this->porAluno($alunoId),
            fn (array $nota): bool => (int) $nota["disciplina_id"] === $disciplinaId
        );

        if (!$notas) {
            return null;
        }

        return array_sum(array_column($notas, "valor")) / count($notas);
    }

    public function situacao(int $alunoId, int $disciplinaId): string
    {
        $disciplina = $this->buscarRegistro("disciplinas", $disciplinaId);

        if ($disciplina === null) {
            throw new \InvalidArgumentException("Disciplina não encontrada.");
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

    private function proximoId(): int
    {
        $ids = array_column($this->todos(), "id");

        return $ids ? max($ids) + 1 : 1;
    }

    private function registroExiste(string $chave, int $id): bool
    {
        return $this->buscarRegistro($chave, $id) !== null;
    }

    private function buscarRegistro(string $chave, int $id): ?array
    {
        foreach ($_SESSION[$chave] ?? [] as $registro) {
            if ((int) $registro["id"] === $id) {
                return $registro;
            }
        }

        return null;
    }
}
