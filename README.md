# Sistema Acadêmico MVC em PHP

Projeto das Avaliações II e III de Linguagem de Programação PHP, feito em cima da estrutura MVC que vimos na Aula 8. Na Avaliação II tudo ficava salvo na sessão; na III a gente passou tudo pro MySQL usando PDO, sem mexer nas rotas nem nas telas.

## Integrantes e responsabilidades

- Guilherme Henrique Canducci: Model Aluno, AlunoController e views de alunos.
- Diogo Berçocano: Model Disciplina, DisciplinaController e views de disciplinas.
- Thiago Henrique Piovan Costa: Model Nota, NotaController e views de notas.

## Estrutura compartilhada

- `index.php`: ponto de entrada, inicialização da sessão e roteamento.
- `app/Views/Render.php`: carrega a view solicitada e a insere no layout.
- `app/Views/layout.php`: estrutura visual comum feita com Bootstrap.
- `composer.json`: configura o autoload PSR-4 das classes em `app/`.
- `app/Config/Database.php`: abre a conexão PDO com o MySQL, uma vez só por requisição.
- `schema.sql`: cria as tabelas `alunos`, `disciplinas` e `notas`, com as chaves estrangeiras.
- MySQL/PDO: onde ficam os dados dos três módulos. A sessão agora só guarda as mensagens de aviso e o que foi digitado num formulário que deu erro.

## Como executar

1. Coloque o projeto na pasta `htdocs/mvc-php` do XAMPP.
2. Na raiz do projeto, execute `composer install`.
3. Inicie o Apache e o MySQL no XAMPP e crie o banco seguindo a seção "Instalação no XAMPP" logo abaixo.
4. Acesse `http://localhost/mvc-php/`.

## Avaliação III: tudo no MySQL com PDO

Agora os três módulos (alunos, disciplinas e notas) salvam e leem os dados direto do MySQL. Cada um fez a migração da sua parte usando a mesma conexão compartilhada, e as rotas, os controllers e as telas continuaram funcionando do mesmo jeito.

### Requisitos

- PHP 8.2 ou superior com as extensões `PDO` e `pdo_mysql`.
- Composer.
- Apache e MySQL/MariaDB do XAMPP.
- Banco com suporte a InnoDB, chaves estrangeiras e restrições `CHECK`.

### Instalação no XAMPP

1. Coloque o projeto em `C:\xampp\htdocs\mvc-php`.
2. Execute `composer install` na raiz do projeto.
3. Inicie Apache e MySQL no painel do XAMPP.
4. No phpMyAdmin, crie o banco `sistema_academico` com collation `utf8mb4_unicode_ci`.
5. Selecione esse banco e importe `schema.sql`. O script cria as tabelas sem executar `DROP DATABASE` ou `DROP TABLE`.
6. Copie `config/database.example.php` para `config/database.php` e ajuste apenas a cópia local. Esse arquivo está ignorado pelo Git.
7. Acesse `http://localhost/mvc-php/`.

Também é possível configurar a conexão por variáveis de ambiente. Elas têm prioridade sobre o arquivo local:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sistema_academico
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

Não inclua senhas reais em commits. A conexão compartilhada é reutilizada durante a requisição e deve ser obtida assim:

```php
use App\Config\Database;

$pdo = Database::getConnection();
```

Os três Models usam essa mesma classe, então ninguém abre uma segunda conexão.

### Módulo de notas com PDO

- CRUD de notas em MySQL com prepared statements e IDs `AUTO_INCREMENT`.
- Validação de aluno, disciplina e intervalo da nota no Model e no banco.
- Consultas por aluno e disciplina, média e situação conforme `media_minima`.
- Formulários, listagem e API JSON de notas usando PDO.
- Chaves estrangeiras com exclusão restrita para preservar notas relacionadas.

### Módulo de disciplinas com PDO

- `app/Models/Disciplina.php` usa `Database::getConnection()` com prepared statements para listar, buscar por ID, buscar por nome (sem diferenciar maiúsculas e acentos), cadastrar, atualizar e excluir.
- As validações continuam no Model: nome de 3 a 100 caracteres sem repetição e média mínima entre 0 e 10.
- Relacionamento com notas: `alunosComMedia()` faz `JOIN` entre `notas`, `alunos` e `disciplinas` e calcula a média com `AVG` no banco, usado na tela de detalhe da disciplina.
- A exclusão de disciplina com notas é bloqueada no controller e também pela chave estrangeira `fk_notas_disciplina` (`ON DELETE RESTRICT`).
- API `/api/disciplinas`: `GET` (todas, `?id=` ou `?q=`), `POST`, `PUT ?id=` e `DELETE ?id=`.


### Módulo de alunos com PDO

- O `app/Models/Aluno.php` saiu da sessão e agora conversa com a tabela `alunos` pelo `Database::getConnection()`, sempre com prepared statements. Dá pra listar, buscar por ID, buscar por nome, cadastrar, editar e excluir.
- As regras continuam as mesmas: o nome é obrigatório, tem que ter de 3 a 100 caracteres e não pode repetir. "Maria Souza" e "maria souza" contam como o mesmo nome, e a busca acha "João" mesmo se você digitar "joao".
- Relação com as notas: o `disciplinasComMedia()` junta `notas` e `disciplinas` com `JOIN`, calcula a média no próprio banco com `AVG` e devolve a situação (Aprovado/Reprovado). É isso que aparece na tela de detalhe do aluno.
- Não dá pra excluir aluno que já tem nota: o controller avisa antes, e se mesmo assim chegar no banco, a chave estrangeira `fk_notas_aluno` (`ON DELETE RESTRICT`) barra.
- API `/api/alunos`: `GET` (todos, `?id=` ou `?q=`), `POST`, `PUT ?id=` e `DELETE ?id=`.


### Testes

Os testes de lógica e do controller usam SQLite em memória e não alteram o banco do XAMPP:

```powershell
composer test

# Alternativa sem o comando Composer disponível no PATH:
C:\xampp\php\php.exe tests\NotaModelTest.php
C:\xampp\php\php.exe tests\NotaControllerTest.php
C:\xampp\php\php.exe tests\DisciplinaModelTest.php
C:\xampp\php\php.exe tests\AlunoModelTest.php
```

Para o teste de integração, importe primeiro o schema em um banco de teste ou desenvolvimento, configure a conexão e confirme explicitamente a execução:

```powershell
$env:RUN_DB_TESTS='1'
$env:DB_NAME='sistema_academico'
C:\xampp\php\php.exe tests\NotaMysqlTest.php
Remove-Item Env:RUN_DB_TESTS
```

O teste MySQL abre uma transação, cria dados fictícios, verifica CRUD, média, situação, validações e chave estrangeira, e executa rollback ao final.

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

- commit: 5a139f7 - Persistência com MySQL e PDO: migração do Model Disciplina de `$_SESSION` para MySQL/PDO usando a conexão compartilhada `Database`, consulta com `JOIN` e `AVG` para alunos e médias da disciplina, rota `PUT /api/disciplinas` para completar o CRUD na API, remoção da sessão de disciplinas do `index.php` e testes em `tests/DisciplinaModelTest.php`.

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

- 256ea62 commit: Persistência com MySQL e PDO: migração do Model Aluno da sessão para MySQL/PDO.
  O Model passou a usar a conexão compartilhada `Database`, com consulta `JOIN` + `AVG` para mostrar as disciplinas, notas e médias do aluno na tela de detalhe. Também entrou a rota `PUT /api/alunos` pra fechar o CRUD na API, saiu a sessão de alunos do `index.php` e foram criados os testes em `tests/AlunoModelTest.php`.
