<?php
    namespace App\Views;

    class Render {
        function render(
            string $view,
            array $params = []
        ){
            //extrair todos os parametros
            //para criar variaveis
            extract($params);

            // start cache ob_start
            ob_start();

            //chamar os arquivos de views
            require __DIR__ . "/$view.php";

            //colocar o conteudo da view
            //dentro de uma variavel
            $conteudo = ob_get_clean();

            ob_start();
            //renderizar o html da master page
            require __DIR__ . "/layout.php";
            return ob_get_clean();
        }
    }

?>