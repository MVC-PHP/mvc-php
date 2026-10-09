<?php

namespace App\Controllers;

use App\Models\Nota;
use App\Views\Render;
use InvalidArgumentException;

class NotaController
{
    private Nota $model;

    public function __construct()
    {
        $this->model = new Nota();
    }

    public function index(): string
    {
        $notas = [];

        foreach ($this->model->todos() as $nota) {
            $nota["aluno"] = $this->buscarRegistro("alunos", (int) $nota["aluno_id"]);
            $nota["disciplina"] = $this->buscarRegistro("disciplinas", (int) $nota["disciplina_id"]);
            $notas[] = $nota;
        }

        return (new Render())->render("notas/index", [
            "titulo" => "Notas",
            "notas" => $notas,
        ]);
    }

    public function criar(): string
    {
        return (new Render())->render("notas/criar", [
            "titulo" => "Nova nota",
            "alunos" => $_SESSION["alunos"] ?? [],
            "disciplinas" => $_SESSION["disciplinas"] ?? [],
        ]);
    }

    public function editar(): string
    {
        $id = (int) ($_GET["id"] ?? 0);
        $nota = $this->model->buscarPorId($id);

        if ($nota === null) {
            $this->mensagem("danger", "Nota não encontrada.");
            $this->redirecionar("/notas");
        }

        return (new Render())->render("notas/editar", [
            "titulo" => "Editar nota",
            "nota" => $nota,
            "alunos" => $_SESSION["alunos"] ?? [],
            "disciplinas" => $_SESSION["disciplinas"] ?? [],
        ]);
    }

    public function salvar(): void
    {
        $id = (int) ($_POST["id"] ?? 0);
        $dados = [
            "aluno_id" => $_POST["aluno_id"] ?? null,
            "disciplina_id" => $_POST["disciplina_id"] ?? null,
            "valor" => $_POST["valor"] ?? null,
        ];

        try {
            if ($id > 0) {
                $this->model->atualizar($id, $dados);
                $texto = "Nota atualizada com sucesso.";
            } else {
                $this->model->criar($dados);
                $texto = "Nota cadastrada com sucesso.";
            }

            $this->mensagem("success", $texto);
            $this->redirecionar("/notas");
        } catch (InvalidArgumentException $erro) {
            $this->mensagem("danger", $erro->getMessage());
            $this->redirecionar($id > 0 ? "/notas/editar?id=$id" : "/notas/criar");
        }
    }

    public function excluir(): void
    {
        $id = (int) ($_POST["id"] ?? 0);

        if ($this->model->excluir($id)) {
            $this->mensagem("success", "Nota excluída com sucesso.");
        } else {
            $this->mensagem("danger", "Nota não encontrada.");
        }

        $this->redirecionar("/notas");
    }

    public function list(): string
    {
        return $this->json($this->model->todos());
    }

    public function create(): string
    {
        try {
            $nota = $this->model->criar($this->dadosApi());

            return $this->json($nota, 201);
        } catch (InvalidArgumentException $erro) {
            return $this->json(["erro" => $erro->getMessage()], 422);
        }
    }

    public function delete(): string
    {
        $id = (int) ($_GET["id"] ?? 0);

        if (!$this->model->excluir($id)) {
            return $this->json(["erro" => "Nota não encontrada."], 404);
        }

        return $this->json(["mensagem" => "Nota excluída com sucesso."]);
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
