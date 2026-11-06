# Avaliação IV — parte de Thiago

## Funcionalidades e rotas

- `GET /relatorios?status=todos|aprovados|reprovados`: médias por aluno e disciplina, com situação calculada pela média mínima. Exige login; ambos os perfis podem acessar.
- `GET /relatorios/exportar?status=...`: mesmos critérios em CSV UTF-8 com BOM e separador `;`. Exige login; ambos os perfis podem acessar.
- `GET /api/notas`: lista as notas; `GET /api/notas?id=1` consulta uma nota.
- `PUT /api/notas?id=1`: atualiza a nota a partir de JSON `{"aluno_id":1,"disciplina_id":1,"valor":7.5}`. Exige administrador. IDs e dados inválidos retornam 422; nota ausente retorna 404.

## Como testar

Entre em `http://localhost/mvc-php/login`, abra `/mvc-php/relatorios`, altere o filtro e use **Baixar CSV**. Compare as linhas do arquivo com a tabela filtrada. Para a API, autentique-se no navegador ou cliente HTTP com cookie de sessão e use as rotas acima. Usuário comum deve receber 403 no PUT; sem login, 401.

As buscas de alunos e disciplinas agora usam `WHERE nome LIKE :termo` e escape literal para `%`, `_` e `!`. Em MySQL, usam `utf8mb4_unicode_ci`, a collation declarada em `schema.sql`, para ignorar acentos e caixa. Os testes SQLite registram uma função `LIKE` equivalente para os caracteres dos casos de teste, pois a implementação padrão do SQLite não reproduz a collation do MySQL.

## Testes e integração

Executar `composer test` ou cada script em `tests/`, além de `php -l` nos arquivos alterados. Os testes SQLite cobrem agregação, filtros, CSV, GET e PUT da API e buscas. A autorização 401/403 depende do roteador existente em `index.php` e precisa de verificação HTTP com sessões reais. O MySQL local não respondeu na porta padrão durante a implementação; a collation e as consultas devem ser verificadas na instância XAMPP usada pelo grupo. Não foi alterada a configuração do banco, o schema ou o tratamento centralizado de exceções reservado a Diogo.

Ao integrar com o trabalho de Diogo, conferir eventuais alterações simultâneas em `index.php` e manter as novas rotas antes do fallback 404. O README será atualizado após a integração das partes.
