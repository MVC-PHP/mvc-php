<?php
    namespace App\Controllers;

    use App\Views\Render;

    class CategoriaController {
        //funções de views
        public function index(): string{
            $titulo = "Listar Categorias";
            $categorias = $_SESSION["categorias"];
            return (new Render ())->render(
                'categorias/index', 
                compact('titulo', 'categorias')
            );
        }
        public function criar(): string{
            $titulo = "Nova Categoria";
            return (new Render ())->render(
                'categorias/criar', 
                compact('titulo')
            );
        }

        //funções de APIS
        public function list(){
            //retornar um JSON
            header("Content-Type: application/json; charset=UTF-8");
            return json_encode($_SESSION["categorias"]);
        }
        public function create(){
            if(isset($_POST["nome"])) {
                //simular o autoincremento
                $id = count(array_column($_SESSION["categorias"], "id"))+1;
                //adicionar um elemento
                $_SESSION["categorias"][] = [
                    "id" => $id,
                    "nome" => $_POST["nome"]
                ];
            }
        }
    }
?>