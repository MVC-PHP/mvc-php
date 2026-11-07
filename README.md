# Sistema Acadêmico — Avaliação IV (PHP MVC)

MVP acadêmico de gestão de alunos, disciplinas e notas em PHP 8 com arquitetura MVC simples, MySQL/MariaDB e PDO. Desenvolvido ao longo das Avaliações II e III e ampliado na Avaliação IV com autenticação, perfis, relatórios, exportação CSV e tratamento de erros.

**Tema:** Sistema Acadêmico  
**Integrantes:** Guilherme Henrique Canducci, Diogo Berçocano e Thiago Henrique Piovan Costa.  
**Versão da entrega:** [branch `staging`](https://github.com/MVC-PHP/mvc-php/tree/staging).

## Funcionalidades

- CRUD de alunos e disciplinas: cadastro, listagem, pesquisa, detalhes, edição e exclusão.
- CRUD de notas vinculadas a alunos e disciplinas; cálculo de médias e situação de aprovação.
- Busca parcial de alunos e disciplinas diretamente no SQL, com consultas PDO parametrizadas.
- Login e logout com sessões PHP e verificação de senha via `password_verify()`.
- Perfis **administrador** (consulta e alterações) e **usuário comum** (consulta).
- Rotas protegidas: acesso não autenticado à API recebe HTTP 401; alteração por usuário comum recebe HTTP 403.
- API JSON para listagem, consulta individual, cadastro, atualização e exclusão das três entidades.
- Relatório acadêmico por aluno/disciplina, com médias e filtro por todos, aprovados e reprovados.
- Exportação CSV do relatório com o filtro aplicado, separador `;` e UTF-8 com BOM.
- Validações no servidor, controle de integridade referencial, mensagens de feedback e página amigável para erros inesperados.

## Requisitos

- PHP **8.2 ou superior**, com extensões `pdo`, `pdo_mysql` e `mbstring`.
- Apache, MySQL/MariaDB e Composer (por exemplo, pelo XAMPP).
- `pdo_sqlite` para executar os testes automatizados em memória.
- Banco com InnoDB, chaves estrangeiras e suporte a restrições `CHECK`.

O projeto utiliza PHP puro e Bootstrap, sem Laravel.

## Estrutura do projeto

| Caminho | Função |
| --- | --- |
| `index.php` | Roteamento, proteção de acesso e tratamento centralizado de exceções |
| `config.php` | Configuração do MySQL, com valores padrão para XAMPP |
| `app/Config/Database.php` | Conexão PDO compartilhada (`ERRMODE_EXCEPTION`, `FETCH_ASSOC`) |
| `app/Models/` | Models Aluno, Disciplina, Nota, Usuario e Relatorio |
| `app/Controllers/` | Controllers web, autenticação, API e relatórios |
| `app/Views/` | Telas Bootstrap e página de erro |
| `database/schema.sql` | Banco e tabelas `alunos`, `disciplinas`, `notas` e `usuarios`, com FKs |
| `database/seed.sql` | Dados de demonstração e usuários de teste |
| `tests/` | Testes de Models, Controllers e Avaliação IV |
| `composer.json` | Autoload PSR-4 e comandos de teste |

## Instalação no XAMPP

1. Baixe a branch `staging` e coloque o projeto em `C:\xampp\htdocs\mvc-php`.
2. Abra um terminal na raiz e execute `composer install`.
3. Inicie Apache e MySQL no painel do XAMPP.
4. No phpMyAdmin, importe **primeiro** `database/schema.sql`. Esse script cria, se necessário, o banco `sistema_academico` e seleciona-o.
5. Importe **depois** `database/seed.sql` para cadastrar alunos, disciplinas, notas e usuários de demonstração. **Execute o seed em um banco vazio**: os IDs e e-mails são fixos, e repetir a importação pode gerar duplicações.
6. Confira `config.php` na raiz. Os valores padrão são host `127.0.0.1`, porta `3306`, banco `sistema_academico`, usuário `root`, senha vazia e charset `utf8mb4`.
7. Abra <http://localhost/mvc-php/> e entre com uma das contas abaixo.

A classe `Database.php` também aceita `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` e `DB_CHARSET` por variáveis de ambiente. Valores de ambiente não vazios têm prioridade sobre `config.php`. Não publique senhas pessoais no repositório.

**Atenção:** as URLs usam o prefixo `/mvc-php` definido em `index.php`; se a pasta receber outro nome, o prefixo precisará ser ajustado na aplicação.

## Usuários de demonstração

Depois de importar `database/seed.sql`:

| Perfil | E-mail | Senha | Acesso |
| --- | --- | --- | --- |
| Administrador | `admin@escola.com` | `admin123` | Listar, consultar, cadastrar, editar e excluir |
| Usuário comum | `usuario@escola.com` | `usuario123` | Listar, consultar, pesquisar, visualizar relatórios e exportar CSV |

As senhas são armazenadas como hashes no banco. Essas credenciais são exclusivas para a demonstração acadêmica.

## Rotas web

As rotas abaixo usam o prefixo `http://localhost/mvc-php`.

| Método | Rota | Finalidade |
| --- | --- | --- |
| GET | `/` | Página inicial |
| GET / POST | `/login` | Exibir formulário / autenticar |
| POST | `/logout` | Encerrar sessão |
| GET | `/alunos` | Listar e buscar usando `?q=` |
| GET | `/alunos/detalhe?id=1` | Detalhes e médias do aluno |
| GET | `/alunos/criar`, `/alunos/editar?id=1` | Formulários (admin) |
| POST | `/alunos/salvar`, `/alunos/excluir` | Gravar ou excluir aluno (admin) |
| GET | `/disciplinas` | Listar e buscar usando `?q=` |
| GET | `/disciplinas/detalhe?id=1` | Detalhes e médias da disciplina |
| GET | `/disciplinas/criar`, `/disciplinas/editar?id=1` | Formulários (admin) |
| POST | `/disciplinas/salvar`, `/disciplinas/excluir` | Gravar ou excluir disciplina (admin) |
| GET | `/notas` | Listar notas |
| GET | `/notas/criar`, `/notas/editar?id=1` | Formulários (admin) |
| POST | `/notas/salvar`, `/notas/excluir` | Gravar ou excluir nota (admin) |
| GET | `/relatorios?status=todos` | Relatório com filtro `todos`, `aprovados` ou `reprovados` |
| GET | `/relatorios/exportar?status=todos` | Baixar CSV com o filtro aplicado |

Página inicial e login são públicos. As demais páginas exigem login. Ações de escrita e formulários de criação/edição exigem administrador.

## API JSON

Base: `http://localhost/mvc-php`. As rotas `/api/*` exigem sessão autenticada; os métodos de escrita exigem perfil administrador. Respostas JSON usam `Content-Type: application/json; charset=UTF-8`.

| Entidade | GET (lista, detalhe e busca) | POST | PUT | DELETE |
| --- | --- | --- | --- | --- |
| Alunos | `/api/alunos`, `/api/alunos?id=1`, `/api/alunos?q=Ana` | `/api/alunos` | `/api/alunos?id=1` | `/api/alunos?id=1` |
| Disciplinas | `/api/disciplinas`, `/api/disciplinas?id=1`, `/api/disciplinas?q=PHP` | `/api/disciplinas` | `/api/disciplinas?id=1` | `/api/disciplinas?id=1` |
| Notas | `/api/notas`, `/api/notas?id=1` | `/api/notas` | `/api/notas?id=1` | `/api/notas?id=1` |

Exemplos de corpos JSON para cadastro/alteração:

```json
{"nome": "Maria Silva"}
```

```json
{"nome": "Programação Web", "media_minima": 6.0}
```

```json
{"aluno_id": 1, "disciplina_id": 1, "valor": 8.5}
```

Entre os códigos de resposta utilizados estão `200` para consulta/atualização, `201` para criação, `401` sem autenticação, `403` sem permissão, `404` para registros inexistentes, `422` para entradas inválidas, `409` para exclusões impedidas por vínculos e `500` em falhas inesperadas.

## Relatório e exportação CSV

Após o login, entre em `/relatorios`. Cada linha mostra aluno, disciplina, média calculada com `AVG(n.valor)`, média mínima e situação. A nota média igual ou superior à média mínima significa **Aprovado**.

O parâmetro `status` aceita `todos` (padrão), `aprovados` e `reprovados`. O botão **Baixar CSV** exporta os mesmos resultados do filtro escolhido nas colunas Aluno, Disciplina, Média, Média mínima e Situação. Tanto o relatório quanto o CSV podem ser acessados pelos dois perfis autenticados.

## Testes

Execute na raiz do projeto:

```powershell
composer test
```

O comando executa os cinco scripts de teste com SQLite em memória:

```powershell
php tests/NotaModelTest.php
php tests/NotaControllerTest.php
php tests/DisciplinaModelTest.php
php tests/AlunoModelTest.php
php tests/AvaliacaoIvTest.php
```

Teste opcional de integração com MySQL (use um banco preparado para testes):

```powershell
$env:RUN_DB_TESTS = '1'
php tests/NotaMysqlTest.php
Remove-Item Env:RUN_DB_TESTS
```

O teste de integração utiliza a conexão configurada e faz rollback ao final. Não execute em um banco com dados importantes sem conferir a configuração.

**Checklist manual:** login/logout; bloqueio de alterações para usuário comum; CRUD de alunos, disciplinas e notas; buscas com acentos; detalhes e médias; filtros do relatório; download CSV; e página amigável em falhas de conexão. A entrega inclui demonstração de **3 a 5 minutos em vídeo ou apresentação em sala**.

## Registro de contribuições

### Thiago Henrique Piovan Costa

- `022ecb2`: base compartilhada do projeto (Composer, sessões, rotas, layout, início e README).
- `ab8e7f7`: Model Nota com CRUD, validações e cálculos.
- `c9260fd`: NotaController e rotas web de notas.
- `fe4e026`: views de listagem, cadastro e edição de notas.
- `a542d0f`: API JSON de notas (GET, POST e DELETE).
- `99ac097`: integração do módulo de Notas com MySQL/PDO à staging.
- `f6d3842`: relatório acadêmico e exportação CSV.
- `bdfab78`: buscas SQL parametrizadas.
- `3fb99b9`: GET individual, PUT e testes da API de notas.
- `ac57ec3`: integração da parte de relatórios/API na staging.

### Diogo Berçocano

- `95136d76`: Model e Controller de Disciplina, validações, views, relação com notas, API e rotas.
- `5a139f7`: migração do módulo Disciplinas para MySQL/PDO, JOIN/AVG, PUT e testes.
- `9419dff`: `config.php`, scripts SQL (`schema` e `seed`), exceções e tratamentos de chaves estrangeiras.

### Guilherme Henrique Canducci

- `2e89b8d`: Model Aluno e validação por nome.
- `8759cac`: AlunoController e rotas web.
- `4ccd21e`: views de Alunos em Bootstrap.
- `b8058d4`: tela de detalhe do aluno com médias.
- `9b516ba`: API JSON de Alunos.
- `256ea62`: migração do módulo Alunos para MySQL/PDO, JOIN/AVG, PUT e testes.
- `3b4760e`: autenticação, logout, rotas protegidas e perfis admin/usuário.
