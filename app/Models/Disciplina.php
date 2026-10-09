<?php
<<<<<<< Updated upstream
    namespace App\Models;

    //acesso a $_SESSION["disciplinas"]
    //campos: id, nome, media_minima
    class Disciplina {
        //todos, buscarPorId, criar, atualizar, excluir, validar
    }
?>
=======

namespace App\Models;

//acesso a $_SESSION["disciplinas"]
//campos: id, nome, media_minima
class Disciplina
{
    public function todos(): array
    {
        return array_values($_SESSION["disciplinas"] ?? []);
    }

    public function buscarPorId(int $id): ?array
    {
        foreach ($this->todos() as $disciplina) {
            if ((int) $disciplina["id"] === $id) {
                return $disciplina;
            }
        }

        return null;
    }

    //busca pelo nome ignorando maiúscula e acento ("alg" acha "Álgebra")
    public function buscarPorNome(string $termo): array
    {
        $termo = $this->simplificar($termo);

        return array_values(array_filter(
            $this->todos(),
            fn (array $disciplina): bool => str_contains($this->simplificar($disciplina["nome"]), $termo)
        ));
    }

    public function criar(array $dados): array
    {
        $erros = $this->validar($dados);

        if ($erros) {
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $_SESSION["disciplinas"] ??= [];

        $disciplina = $this->normalizarDados($dados);
        $disciplina["id"] = $this->proximoId();

        $_SESSION["disciplinas"][] = $disciplina;

        return $disciplina;
    }

    public function atualizar(int $id, array $dados): array
    {
        if ($this->buscarPorId($id) === null) {
            throw new \InvalidArgumentException("Disciplina não encontrada.");
        }

        //passa o $id pra não acusar nome repetido dela mesma
        $erros = $this->validar($dados, $id);

        if ($erros) {
            throw new \InvalidArgumentException(implode(" ", $erros));
        }

        $atualizada = $this->normalizarDados($dados);
        $atualizada["id"] = $id;

        foreach ($_SESSION["disciplinas"] as $indice => $disciplina) {
            if ((int) $disciplina["id"] === $id) {
                $_SESSION["disciplinas"][$indice] = $atualizada;
                break;
            }
        }

        return $atualizada;
    }

    public function excluir(int $id): bool
    {
        $quantidadeAnterior = count($this->todos());

        $_SESSION["disciplinas"] = array_values(array_filter(
            $this->todos(),
            fn (array $disciplina): bool => (int) $disciplina["id"] !== $id
        ));

        return count($_SESSION["disciplinas"]) < $quantidadeAnterior;
    }

    //devolve a lista de erros (vazia = dados válidos)
    public function validar(array $dados, ?int $ignorarId = null): array
    {
        $erros = [];

        $nome = trim((string) ($dados["nome"] ?? ""));
        //aceita vírgula ou ponto (6,5 ou 6.5)
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

    private function proximoId(): int
    {
        $ids = array_column($this->todos(), "id");

        return $ids ? max($ids) + 1 : 1;
    }

    private function nomeJaExiste(string $nome, ?int $ignorarId): bool
    {
        foreach ($this->todos() as $disciplina) {
            if ($ignorarId !== null && (int) $disciplina["id"] === $ignorarId) {
                continue;
            }

            if ($this->simplificar($disciplina["nome"]) === $this->simplificar($nome)) {
                return true;
            }
        }

        return false;
    }

    //minúsculo e sem acento, pra comparar textos
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
>>>>>>> Stashed changes
