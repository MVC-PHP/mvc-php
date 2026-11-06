<?php

require_once __DIR__ . "/vendor/autoload.php";

use App\Controllers\AlunoController;
use App\Controllers\DisciplinaController;
use App\Controllers\NotaController;
use App\Controllers\AuthController;
use App\Controllers\RelatorioController;
use App\Views\Render;

session_start();

$basedir = "/mvc-php";
define("BASE_URL", $basedir);

$url = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?: "/";

if (str_starts_with($url, $basedir)) {
    $url = substr($url, strlen($basedir));
}

$url = "/" . trim($url, "/");
$metodo = $_SERVER["REQUEST_METHOD"] ?? "GET";
$render = new Render();

function tratarErro(Throwable $erro, string $url, Render $render): void
{
    error_log($erro::class . ": " . $erro->getMessage());

    $mensagem = $erro instanceof PDOException
        ? "Não foi possível acessar o banco de dados. Verifique se o MySQL está em execução e se o config.php está correto."
        : "Ocorreu um erro inesperado. Tente novamente em instantes.";

    http_response_code(500);

    if (str_starts_with($url, "/api/")) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(["erro" => $mensagem], JSON_UNESCAPED_UNICODE);
        return;
    }

    echo $render->render("erro", [
        "titulo" => "Erro",
        "mensagemErro" => $mensagem,
    ]);
}

try {
    if ($url === "/login") {
        if ($metodo === "GET") {
            echo (new AuthController())->mostrarLogin();
            exit;
        }
        if ($metodo === "POST") {
            (new AuthController())->entrar();
        }
    }

    if ($metodo === "POST" && $url === "/logout") {
        (new AuthController())->sair();
    }

    $rotasPublicas = ["/", "/login"];
    $logado = isset($_SESSION["usuario"]);
    if (!$logado && !in_array($url, $rotasPublicas, true)) {
        if (str_starts_with($url, "/api/")) {
            http_response_code(401);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(["erro" => "Faça login para acessar a API."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION["flash"] = ["tipo" => "warning", "mensagem" => "Faça login para continuar."];
        header("Location: " . BASE_URL . "/login");
        exit;
    }

    $ehAdmin = ($_SESSION["usuario"]["perfil"] ?? "") === "admin";
    $rotaDeEscrita = $metodo !== "GET" 
    || str_ends_with($url, "/criar")
    || str_ends_with($url, "/editar");

    if ($logado && $rotaDeEscrita && !$ehAdmin && !in_array($url, ["/login", "/logout"], true)) {
        if (str_starts_with($url, "/api/")) {
            http_response_code(403);
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(["erro" => "Apenas administradores podem fazer isso."], 
            JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION["flash"] = ["tipo" => "danger", "mensagem" => "Apenas administradores podem fazer 
        isso."];
        header("Location: " . BASE_URL . "/");
        exit;
    }




    if ($metodo === "GET" && $url === "/") {
        echo $render->render("inicio", [
            "titulo" => "Sistema Acadêmico",
        ]);
        exit;
    }

    if ($metodo === "GET") {
        if ($url === "/relatorios") {
            echo (new RelatorioController())->index();
            exit;
        }
        if ($url === "/relatorios/exportar") {
            (new RelatorioController())->exportar();
            exit;
        }
        if ($url === "/notas") {
            echo (new NotaController())->index();
            exit;
        }

        if ($url === "/notas/criar") {
            echo (new NotaController())->criar();
            exit;
        }

        if ($url === "/notas/editar") {
            echo (new NotaController())->editar();
            exit;
        }
    }

    if ($metodo === "POST") {
        if ($url === "/notas/salvar") {
            (new NotaController())->salvar();
        }

        if ($url === "/notas/excluir") {
            (new NotaController())->excluir();
        }
    }

    if ($url === "/api/notas") {
        if ($metodo === "GET") {
            echo (new NotaController())->list();
            exit;
        }

        if ($metodo === "POST") {
            echo (new NotaController())->create();
            exit;
        }

        if ($metodo === "DELETE") {
            echo (new NotaController())->delete();
            exit;
        }
        if ($metodo === "PUT") {
            echo (new NotaController())->update();
            exit;
        }
    }

    if ($metodo === "GET") {
        if ($url === "/disciplinas") {
            echo (new DisciplinaController())->index();
            exit;
        }

        if ($url === "/disciplinas/criar") {
            echo (new DisciplinaController())->criar();
            exit;
        }

        if ($url === "/disciplinas/editar") {
            echo (new DisciplinaController())->editar();
            exit;
        }

        if ($url === "/disciplinas/detalhe") {
            echo (new DisciplinaController())->detalhe();
            exit;
        }
    }

    if ($metodo === "POST") {
        if ($url === "/disciplinas/salvar") {
            (new DisciplinaController())->salvar();
        }

        if ($url === "/disciplinas/excluir") {
            (new DisciplinaController())->excluir();
        }
    }

    if ($url === "/api/disciplinas") {
        if ($metodo === "GET") {
            echo (new DisciplinaController())->list();
            exit;
        }

        if ($metodo === "POST") {
            echo (new DisciplinaController())->create();
            exit;
        }

        if ($metodo === "PUT") {
            echo (new DisciplinaController())->update();
            exit;
        }

        if ($metodo === "DELETE") {
            echo (new DisciplinaController())->delete();
            exit;
        }
    }

    if ($metodo === "GET") {
        if ($url === "/alunos") {
            echo (new AlunoController())->index();
            exit;
        }

        if ($url === "/alunos/criar") {
            echo (new AlunoController())->criar();
            exit;
        }

        if ($url === "/alunos/editar") {
            echo (new AlunoController())->editar();
            exit;
        }

        if ($url === "/alunos/detalhe") {
            echo (new AlunoController())->detalhe();
            exit;
        }
    }

    if ($metodo === "POST") {
        if ($url === "/alunos/salvar") {
            (new AlunoController())->salvar();
        }

        if ($url === "/alunos/excluir") {
            (new AlunoController())->excluir();
        }
    }

    if ($url === "/api/alunos") {
        if ($metodo === "GET") {
            echo (new AlunoController())->list();
            exit;
        }

        if ($metodo === "POST") {
            echo (new AlunoController())->create();
            exit;
        }

        if ($metodo === "PUT") {
            echo (new AlunoController())->update();
            exit;
        }

        if ($metodo === "DELETE") {
            echo (new AlunoController())->delete();
            exit;
        }
    }

    http_response_code(404);
    echo $render->render("inicio", [
        "titulo" => "Página não encontrada",
        "paginaNaoEncontrada" => true,
    ]);
} catch (Throwable $erro) {
    tratarErro($erro, $url, $render);
}
