<?php

namespace App\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Nota;
use App\Views\Render;
use InvalidArgumentException;

class AlunoController
{
    private Aluno $model;

    public function __construct()
    {
        $this->model = new Aluno();
    }

    public function index(): string
    {
        $busca = trim($_GET["q"] ?? "");

        return (new Render())->render("alunos/index", [
            "titulo" => "Alunos",
            "alunos" => $busca !== "" ? $this->model->buscarPorNome($busca) : $this->model->todos(),
            "busca" => $busca,
        ]);
    }

    public function criar(): string
    {
        return (new Render())->render("alunos/criar", [
            "titulo" => "Novo aluno",
            "aluno" => $this->dadosDigitados() ?? ["nome" => ""],
        ]);
    }

    public function editar(): string
    {
        $id = (int) ($_GET["id"] ?? 0);
        $aluno = $this->model->buscarPorId($id);

        if ($aluno === null) {
            $this->mensagem("danger", "Aluno não encontrado.");
            $this->redirecionar("/alunos");
        }

        return (new Render())->render("alunos/editar", [
            "titulo" => "Editar aluno",
            "aluno" => ($this->dadosDigitados() ?? []) + $aluno,
        ]);
    }

    public function detalhe(): string
    {
        $id = (int) ($_GET["id"] ?? 0);
        $aluno = $this->model->buscarPorId($id);

        if ($aluno === null) {
            $this->mensagem("danger", "Aluno não encontrado.");
            $this->redirecionar("/alunos");
        }

        return (new Render())->render("alunos/detalhe", [
            "titulo" => "Aluno: " . $aluno["nome"],
            "aluno" => $aluno,
            "disciplinas" => $this->resumoDasDisciplinas($id),
        ]);
    }

    public function salvar(): void
    {
        $id = (int) ($_POST["id"] ?? 0);
        $dados = [
            "nome" => $_POST["nome"] ?? "",
        ];

        try {
            if ($id > 0) {
                $this->model->atualizar($id, $dados);
                $texto = "Aluno atualizado com sucesso.";
            } else {
                $this->model->criar($dados);
                $texto = "Aluno cadastrado com sucesso.";
            }

            $this->mensagem("success", $texto);
            $this->redirecionar("/alunos");
        } catch (InvalidArgumentException $erro) {
            $_SESSION["aluno_digitado"] = $dados;
            $this->mensagem("danger", $erro->getMessage());
            $this->redirecionar($id > 0 ? "/alunos/editar?id=$id" : "/alunos/criar");
        }
    }

    public function excluir(): void
    {
        $id = (int) ($_POST["id"] ?? 0);

        if ($this->model->buscarPorId($id) === null) {
            $this->mensagem("danger", "Aluno não encontrado.");
        } elseif ($this->temNotas($id)) {
            $this->mensagem("danger", "Não dá pra excluir: o aluno já tem notas lançadas.");
        } else {
            $this->model->excluir($id);
            $this->mensagem("success", "Aluno excluído com sucesso.");
        }

        $this->redirecionar("/alunos");
    }

    public function list(): string
    {
        if (isset($_GET["id"])) {
            $aluno = $this->model->buscarPorId((int) $_GET["id"]);

            return $aluno === null
                ? $this->json(["erro" => "Aluno não encontrado."], 404)
                : $this->json($aluno);
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
            return $this->json(["erro" => "Aluno não encontrado."], 404);
        }

        if ($this->temNotas($id)) {
            return $this->json(["erro" => "O aluno já tem notas lançadas."], 409);
        }

        $this->model->excluir($id);

        return $this->json(["mensagem" => "Aluno excluído com sucesso."]);
    }

    private function resumoDasDisciplinas(int $alunoId): array
    {
        $nota = new Nota();
        $disciplinaModel = new Disciplina();
        $notasPorDisciplina = [];

        foreach ($nota->porAluno($alunoId) as $registro) {
            $notasPorDisciplina[(int) $registro["disciplina_id"]][] = (float) $registro["valor"];
        }

        $linhas = [];

        foreach ($notasPorDisciplina as $disciplinaId => $valores) {
            $disciplina = $disciplinaModel->buscarPorId($disciplinaId);

            $linhas[] = [
                "nome" => $disciplina["nome"] ?? "Disciplina #$disciplinaId",
                "media_minima" => $disciplina["media_minima"] ?? null,
                "notas" => $valores,
                "media" => $nota->media($alunoId, $disciplinaId),
                "situacao" => $disciplina !== null ? $nota->situacao($alunoId, $disciplinaId) : "Sem disciplina",
            ];
        }

        return $linhas;
    }

    private function temNotas(int $id): bool
    {
        return (new Nota())->porAluno($id) !== [];
    }

    private function dadosDigitados(): ?array
    {
        $dados = $_SESSION["aluno_digitado"] ?? null;
        unset($_SESSION["aluno_digitado"]);

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
