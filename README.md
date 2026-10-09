# Sistema Acadêmico MVC em PHP

Projeto da Avaliação II de Linguagem de Programação PHP, desenvolvido com base na estrutura MVC apresentada na Aula 8.

## Integrantes e responsabilidades

- Guilherme Henrique Canducci: Model Aluno, AlunoController e views de alunos.
- Diogo Berçocano: Model Disciplina, DisciplinaController e views de disciplinas.
- Thiago Henrique Piovan Costa: Model Nota, NotaController e views de notas.

## Estrutura compartilhada

- `index.php`: ponto de entrada, inicialização da sessão e roteamento.
- `app/Views/Render.php`: carrega a view solicitada e a insere no layout.
- `app/Views/layout.php`: estrutura visual comum feita com Bootstrap.
- `composer.json`: configura o autoload PSR-4 das classes em `app/`.
- `$_SESSION["alunos"]`, `$_SESSION["disciplinas"]` e `$_SESSION["notas"]`: dados usados pela aplicacao.

## Como executar

1. Coloque o projeto na pasta `htdocs/mvc-php` do XAMPP.
2. Na raiz do projeto, execute `composer install`.
3. Inicie o Apache no XAMPP.
4. Acesse `http://localhost/mvc-php/`.

## Registro de contribuições

### Thiago Henrique Piovan Costa

Registrar uma linha depois de cada commit realizado:

- commit: 022ecb2 - Configuração da base compartilhada do projeto seguindo a Aula 8: autoload do Composer, inicialização das sessões de alunos, disciplinas e notas, roteamento inicial no index.php, layout responsivo com Bootstrap, página inicial, tratamento de rota inexistente e documentação no README.
