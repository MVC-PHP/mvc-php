<?php

namespace App\Models;

class Aluno
{
    public function todos(): array
    {
        return array_values($_SESSION["alunos"] ?? []);
    }

    public function buscarPorId(int $id): ?array
    {
        foreach ($this->todos() as $aluno) {
            if ((int) $aluno["id"] === $id) {
                return $aluno;
            }
        }

        return null;
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
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $_SESSION["alunos"] ??= [];

        $aluno = $this->normalizarDados($dados);
        $aluno["id"] = $this->proximoId();

        $_SESSION["alunos"][] = $aluno;

        return $aluno;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new \InvalidArgumentException("Aluno não encontrado.");
        }

        $erros = $this->validar($dados, $id);

        if ($erros) {
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $atualizado = $this->normalizarDados($dados);
        $atualizado["id"] = $id;

        foreach ($_SESSION["alunos"] as $indice => $aluno) {
            if ((int) $aluno["id"] === $id) {
                $_SESSION["alunos"][$indice] = $atualizado;
                break;
            }
        }

        return $atualizado;
    }

    public function excluir(int $id): bool
    {
        $quantidadeAnterior = count($this->todos());

        $_SESSION["alunos"] = array_values(array_filter(
            $this->todos(),
            fn (array $aluno): bool => (int) $aluno["id"] !== $id
        ));

        return count($_SESSION["alunos"]) < $quantidadeAnterior;
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

    private function proximoId(): int
    {
        $ids = array_column($this->todos(), "id");

        return $ids ? max($ids) + 1 : 1;
    }

    private function nomeJaExiste(string $nome, ?int $ignorarId): bool
    {
        foreach ($this->todos() as $aluno) {
            if ($ignorarId !== null && (int) $aluno["id"] === $ignorarId) {
                continue;
            }

            if ($this->simplificar($aluno["nome"]) === $this->simplificar($nome)) {
                return true;
            }
        }

        return false;
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
