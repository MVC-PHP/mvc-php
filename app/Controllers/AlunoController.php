<?php

namespace App\Controllers;

use App\Models\Aluno;
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

    private function redirecionar(string $rota): void
    {
        header("Location: " . BASE_URL . $rota);
        exit;
    }
}
