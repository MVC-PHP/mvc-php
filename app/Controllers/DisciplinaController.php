<?php

namespace App\Controllers;

use App\Models\Disciplina;
use App\Models\Nota;
use App\Views\Render;
use InvalidArgumentException;

class DisciplinaController
{
    private Disciplina $model;

    public function __construct()
    {
        $this->model = new Disciplina();
    }

    public function index(): string
    {
        $busca = trim($_GET["q"] ?? "");

        return (new Render())->render("disciplinas/index", [
            "titulo" => "Disciplinas",
            "disciplinas" => $busca !== "" ? $this->model->buscarPorNome($busca) : $this->model->todos(),
            "busca" => $busca,
        ]);
    }

    public function criar(): string
    {
        return (new Render())->render("disciplinas/criar", [
            "titulo" => "Nova disciplina",
            "disciplina" => $this->dadosDigitados() ?? ["nome" => "", "media_minima" => "6"],
        ]);
    }

    public function editar(): string
    {
        $id = (int) ($_GET["id"] ?? 0);
        $disciplina = $this->model->buscarPorId($id);

        if ($disciplina === null) {
            $this->mensagem("danger", "Disciplina não encontrada.");
            $this->redirecionar("/disciplinas");
        }

        return (new Render())->render("disciplinas/editar", [
            "titulo" => "Editar disciplina",
            "disciplina" => ($this->dadosDigitados() ?? []) + $disciplina,
        ]);
    }

    public function detalhe(): string
    {
        $id = (int) ($_GET["id"] ?? 0);
        $disciplina = $this->model->buscarPorId($id);

        if ($disciplina === null) {
            $this->mensagem("danger", "Disciplina não encontrada.");
            $this->redirecionar("/disciplinas");
        }

        return (new Render())->render("disciplinas/detalhe", [
            "titulo" => "Disciplina: " . $disciplina["nome"],
            "disciplina" => $disciplina,
            "alunos" => $this->resumoDosAlunos($disciplina),
        ]);
    }

    public function salvar(): void
    {
        $id = (int) ($_POST["id"] ?? 0);
        $dados = [
            "nome" => $_POST["nome"] ?? "",
            "media_minima" => $_POST["media_minima"] ?? "",
        ];

        try {
            if ($id > 0) {
                $this->model->atualizar($id, $dados);
                $texto = "Disciplina atualizada com sucesso.";
            } else {
                $this->model->criar($dados);
                $texto = "Disciplina cadastrada com sucesso.";
            }

            $this->mensagem("success", $texto);
            $this->redirecionar("/disciplinas");
        } catch (InvalidArgumentException $erro) {
            $_SESSION["disciplina_digitada"] = $dados;
            $this->mensagem("danger", $erro->getMessage());
            $this->redirecionar($id > 0 ? "/disciplinas/editar?id=$id" : "/disciplinas/criar");
        }
    }

    public function excluir(): void
    {
        $id = (int) ($_POST["id"] ?? 0);

        if ($this->model->buscarPorId($id) === null) {
            $this->mensagem("danger", "Disciplina não encontrada.");
        } elseif ($this->temNotas($id)) {
            $this->mensagem("danger", "Não dá pra excluir: a disciplina já tem notas lançadas.");
        } else {
            $this->model->excluir($id);
            $this->mensagem("success", "Disciplina excluída com sucesso.");
        }

        $this->redirecionar("/disciplinas");
    }

    public function list(): string
    {
        if (isset($_GET["id"])) {
            $disciplina = $this->model->buscarPorId((int) $_GET["id"]);

            return $disciplina === null
                ? $this->json(["erro" => "Disciplina não encontrada."], 404)
                : $this->json($disciplina);
        }

        $busca = trim($_GET["q"] ?? "");

        return $this->json($busca !== "" ? $this->model->buscarPorNome($busca) : $this->model->todos());
    }

    public function create(): string
    {
        try {
            return $this->json($this->model->criar($this->dadosApi()), 201);
        } catch (InvalidArgumentException $erro) {
            return $this->json(["erro" => $erro->getMessage()], 422);
        }
    }

    public function delete(): string
    {
        $id = (int) ($_GET["id"] ?? 0);

        if ($this->model->buscarPorId($id) === null) {
            return $this->json(["erro" => "Disciplina não encontrada."], 404);
        }

        if ($this->temNotas($id)) {
            return $this->json(["erro" => "A disciplina já tem notas lançadas."], 409);
        }

        $this->model->excluir($id);

        return $this->json(["mensagem" => "Disciplina excluída com sucesso."]);
    }

    private function resumoDosAlunos(array $disciplina): array
    {
        $nota = new Nota();
        $idDisciplina = (int) $disciplina["id"];
        $linhas = [];

        $alunosIds = array_unique(array_column($nota->porDisciplina($idDisciplina), "aluno_id"));

        foreach ($alunosIds as $alunoId) {
            $aluno = $this->buscarRegistro("alunos", (int) $alunoId);

            $linhas[] = [
                "nome" => $aluno["nome"] ?? "Aluno #$alunoId",
                "media" => $nota->media((int) $alunoId, $idDisciplina),
                "situacao" => $nota->situacao((int) $alunoId, $idDisciplina),
            ];
        }

        return $linhas;
    }

    private function temNotas(int $id): bool
    {
        return (new Nota())->porDisciplina($id) !== [];
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

    private function dadosDigitados(): ?array
    {
        $dados = $_SESSION["disciplina_digitada"] ?? null;
        unset($_SESSION["disciplina_digitada"]);

        return $dados;
    }

    private function mensagem(string $tipo, string $texto): void
    {
        $_SESSION["flash"] = [
            "tipo" => $tipo,
            "mensagem" => $texto,
        ];
    }

    private function dadosApi(): array
    {
        $dados = json_decode(file_get_contents("php://input"), true);

        return is_array($dados) ? $dados : $_POST;
    }

    private function json(array $dados, int $status = 200): string
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");

        return json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function redirecionar(string $rota): void
    {
        header("Location: " . BASE_URL . $rota);
        exit;
    }
}
