<?php

require_once __DIR__ . "/vendor/autoload.php";

use App\Controllers\DisciplinaController;
use App\Controllers\NotaController;
use App\Views\Render;

session_start();

// A sessão funciona como banco de dados durante a atividade.
$_SESSION["alunos"] ??= [];
$_SESSION["disciplinas"] ??= [];
$_SESSION["notas"] ??= [];

$basedir = "/mvc-php";
define("BASE_URL", $basedir);

$url = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ?: "/";

if (str_starts_with($url, $basedir)) {
    $url = substr($url, strlen($basedir));
}

$url = "/" . trim($url, "/");
$metodo = $_SERVER["REQUEST_METHOD"] ?? "GET";
$render = new Render();

if ($metodo === "GET" && $url === "/") {
    echo $render->render("inicio", [
        "titulo" => "Sistema Acadêmico",
    ]);
    exit;
}

if ($metodo === "GET") {
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

    if ($metodo === "DELETE") {
        echo (new DisciplinaController())->delete();
        exit;
    }
}
http_response_code(404);
echo $render->render("inicio", [
    "titulo" => "Página não encontrada",
    "paginaNaoEncontrada" => true,
]);
