<?php $baseUrl = BASE_URL; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Alunos</h1>
        <p class="text-secondary mb-0">Gerencie os alunos cadastrados.</p>
    </div>
    <a class="btn btn-primary" href="<?= $baseUrl ?>/alunos/criar">Novo aluno</a>
</div>

<form class="row g-2 mb-3" method="get" action="<?= $baseUrl ?>/alunos">
    <div class="col-sm-8 col-md-6">
        <input
            class="form-control"
            type="search"
            name="q"
            value="<?= htmlspecialchars($busca) ?>"
            placeholder="Buscar por nome"
        >
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
        <?php if ($busca !== ""): ?>
            <a class="btn btn-light" href="<?= $baseUrl ?>/alunos">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                        <tr>
                            <td><?= htmlspecialchars($aluno["nome"]) ?></td>
                            <td class="text-end text-nowrap">
                                <a
                                    class="btn btn-sm btn-outline-secondary"
                                    href="<?= $baseUrl ?>/alunos/detalhe?id=<?= (int) $aluno["id"] ?>"
                                >
                                    Ver
                                </a>
                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?= $baseUrl ?>/alunos/editar?id=<?= (int) $aluno["id"] ?>"
                                >
                                    Editar
                                </a>
                                <form
                                    class="d-inline"
                                    method="post"
                                    action="<?= $baseUrl ?>/alunos/excluir"
                                    onsubmit="return confirm('Deseja excluir este aluno?')"
                                >
                                    <input type="hidden" name="id" value="<?= (int) $aluno["id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$alunos): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="2">
                                Nenhum aluno encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
