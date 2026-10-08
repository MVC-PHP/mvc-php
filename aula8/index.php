<?php
    //chamando o arquivo de config do composer
    require_once __DIR__ . "/vendor/autoload.php";
    //require_once __DIR__ . "/app/teste.php";

    //chamar as classes 
    use App\Teste;
    use App\Controllers\CategoriaController;

    //fake db
    session_start();
    if (!isset($_SESSION["categorias"])){
        $_SESSION["categorias"] = [
            ["id" => 1, "nome" => "Eletrônicos"],
            ["id" => 2, "nome" => "Livros"],
        ];
    }

    //instacinar a classe teste
    //new Teste();

    //definir uma variavel com a pasta base
    $basedir = "/aula8";

    //pegar a URL crua (sem tratamento)
    $uri = $_SERVER["REQUEST_URI"] ?? "/";
    
    //tratar QueryString (ex: ?parametro=valor)
    $uri = strtok($uri, "?");

    //remover o prefixo aula8 da frente da URI
    if(str_starts_with($uri, $basedir)){
        //remover a string da URI
        $uri = substr($uri, strlen($basedir));
    }

    //remover a barra final da URI
    $uri = rtrim($uri, "/");

    //pegar o metodo da requisição
    $metodo = $_SERVER["REQUEST_METHOD"];

    //criação as rotas de views
    if($metodo === "GET"){
        //rotas de CATEGORIAS
        //listar as CATEGORIAS
        if($uri === "/categorias"){
            echo (new CategoriaController())->index();
            exit;
        }
        //cadastrar as CATEGORIAS
        if($uri === "/categorias/criar"){
            echo (new CategoriaController())->criar();
            exit;
        }
    }
    //rotas de APIs
    //api de listar
    if($uri === "/api/categorias" && $metodo === "GET"){
        echo (new CategoriaController())->list();
        exit;
    }

    //api de criar
    if($uri === "/api/categorias" && $metodo === "POST"){
        echo (new CategoriaController())->create();
        exit;
    }
?>