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

- commit: 022ecb2 - Configuração da base compartilhada do projeto seguindo a Aula 8: autoload do Composer, inicialização das sessões de alunos, disciplinas e notas, roteamento inicial no index.php, layout responsivo com Bootstrap, página inicial, tratamento de rota inexistente e documentação no README.

- commit: ab8e7f7 - Implementação do Model Nota com cadastro, atualização, exclusão, validações, geração automática de IDs, consultas por aluno e disciplina, cálculo de média e situação.

- commit: c9260fd - Implementação do NotaController e das rotas web para listagem, cadastro, edição, salvamento e exclusão de notas.

- commit: fe4e026 - Criação das telas de listagem, cadastro e edição de notas com Bootstrap, aviso quando não existem alunos ou disciplinas e exibição de mensagens da sessão.

- commit: a542d0f - Implementação da API JSON de notas com as rotas GET, POST e DELETE, incluindo respostas de sucesso, validação, registro não encontrado e códigos HTTP adequados.

### Diogo Berçocano

Registrar uma linha depois de cada commit realizado:

- commit: 95136d76c19d030b7acde388aaebd27d090d54b9 - Disciplinas: Model com validação (nome de 3 a 100 caracteres e sem repetição, média mínima entre 0 e 10), busca por nome sem diferenciar maiúsculas e acentos, DisciplinaController com telas (lista com busca, criar, editar e detalhe com alunos e médias vindos das notas) e API em JSON (`/api/disciplinas`: consultar, inserir e excluir), views em Bootstrap com mensagens de sucesso e erro, bloqueio de exclusão de disciplina que já tem notas e rotas de disciplinas no index.php.

### Guilherme Canducci

Commits em MVC-Alunos.

- 2e89b8d commit: Model com validação e busca por nome.
  Implementação do Model Aluno, responsável por ler e gravar os alunos na sessão: listagem, consulta por ID, busca por nome sem diferenciar maiúsculas e acentos, cadastro com geração automática de IDs, atualização e exclusão. Inclui validação do nome (obrigatório, de 3 a 100 caracteres e sem repetição).

- 8759cac commit: Implementação do AlunoController e das rotas web de alunos no index.php.
  Listagem com busca por nome, formulários de cadastro e edição, salvamento com retorno das mensagens de validação e exclusão bloqueada quando o aluno já tem notas lançadas.

- 4ccd21e commit: Criação das telas de alunos com Bootstrap.
  Listagem com busca por nome e ações de editar e excluir, formulário único reaproveitado no cadastro e na edição, aviso de lista vazia e exibição das mensagens de sucesso e erro da sessão.

- b8058d4 commit: Criação da tela de detalhe do aluno, com rota e método no AlunoController.
  Exibe as notas do aluno agrupadas por disciplina, com média, média mínima e situação de aprovação, integrando os Models de Nota e Disciplina. Inclusão do botão "Ver" na listagem de alunos.

- 9b516ba commit: Implementação da API JSON de alunos na rota /api/alunos.
  Consulta (todos, por ID ou por nome), inserção e exclusão, com respostas de sucesso, erro de validação, registro não encontrado, bloqueio de exclusão de aluno com notas e códigos HTTP adequados.
