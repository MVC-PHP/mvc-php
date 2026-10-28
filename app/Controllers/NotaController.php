<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Nota;
use App\Views\Render;
use InvalidArgumentException;
use PDO;
use PDOException;

class NotaController
{
    private Nota $model;
    private PDO $conexao;

    public function __construct(?PDO $conexao = null)
    {
        $this->conexao = $conexao ?? Database::getConnection();
        $this->model = new Nota($this->conexao);
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
            "alunos" => $this->todosRegistros("alunos"),
            "disciplinas" => $this->todosRegistros("disciplinas"),
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
            "alunos" => $this->todosRegistros("alunos"),
            "disciplinas" => $this->todosRegistros("disciplinas"),
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
        } catch (PDOException $erro) {
            error_log($erro->getMessage());
            $this->mensagem("danger", "Não foi possível salvar a nota no banco de dados.");
            $this->redirecionar($id > 0 ? "/notas/editar?id=$id" : "/notas/criar");
        }
    }

    public function excluir(): void
    {
        $id = (int) ($_POST["id"] ?? 0);

        try {
            if ($this->model->excluir($id)) {
                $this->mensagem("success", "Nota excluída com sucesso.");
            } else {
                $this->mensagem("danger", "Nota não encontrada.");
            }
        } catch (PDOException $erro) {
            error_log($erro->getMessage());
            $this->mensagem("danger", "Não foi possível excluir a nota do banco de dados.");
        }

        $this->redirecionar("/notas");
    }

    public function list(): string
    {
        try {
            return $this->json($this->model->todos());
        } catch (PDOException $erro) {
            error_log($erro->getMessage());

            return $this->json(["erro" => "Falha ao acessar o banco de dados."], 500);
        }
    }

    public function create(): string
    {
        try {
            $nota = $this->model->criar($this->dadosApi());

            return $this->json($nota, 201);
        } catch (InvalidArgumentException $erro) {
            return $this->json(["erro" => $erro->getMessage()], 422);
        } catch (PDOException $erro) {
            error_log($erro->getMessage());

            return $this->json(["erro" => "Falha ao acessar o banco de dados."], 500);
        }
    }

    public function delete(): string
    {
        $id = (int) ($_GET["id"] ?? 0);

        try {
            if (!$this->model->excluir($id)) {
                return $this->json(["erro" => "Nota não encontrada."], 404);
            }
        } catch (PDOException $erro) {
            error_log($erro->getMessage());

            return $this->json(["erro" => "Falha ao acessar o banco de dados."], 500);
        }

        return $this->json(["mensagem" => "Nota excluída com sucesso."]);
    }

    private function buscarRegistro(string $tabela, int $id): ?array
    {
        $this->validarTabela($tabela);

        $consulta = $this->conexao->prepare("SELECT * FROM {$tabela} WHERE id = :id");
        $consulta->execute(["id" => $id]);
        $registro = $consulta->fetch();

        return $registro === false ? null : $registro;
    }

    private function todosRegistros(string $tabela): array
    {
        $this->validarTabela($tabela);

        return $this->conexao
            ->query("SELECT * FROM {$tabela} ORDER BY nome, id")
            ->fetchAll();
    }

    private function validarTabela(string $tabela): void
    {
        if (!in_array($tabela, ["alunos", "disciplinas"], true)) {
            throw new InvalidArgumentException("Tabela de relacionamento inválida.");
        }
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
