<?php $baseUrl = BASE_URL; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Disciplinas</h1>
        <p class="text-secondary mb-0">Gerencie as disciplinas e a média mínima de cada uma.</p>
    </div>
    <a class="btn btn-primary" href="<?= $baseUrl ?>/disciplinas/criar">Nova disciplina</a>
</div>

<form class="row g-2 mb-3" method="get" action="<?= $baseUrl ?>/disciplinas">
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
            <a class="btn btn-light" href="<?= $baseUrl ?>/disciplinas">Limpar</a>
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
                        <th>Média mínima</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <tr>
                            <td><?= htmlspecialchars($disciplina["nome"]) ?></td>
                            <td><?= number_format((float) $disciplina["media_minima"], 1, ",", ".") ?></td>
                            <td class="text-end text-nowrap">
                                <a
                                    class="btn btn-sm btn-outline-secondary"
                                    href="<?= $baseUrl ?>/disciplinas/detalhe?id=<?= (int) $disciplina["id"] ?>"
                                >
                                    Ver
                                </a>
                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?= $baseUrl ?>/disciplinas/editar?id=<?= (int) $disciplina["id"] ?>"
                                >
                                    Editar
                                </a>
                                <form
                                    class="d-inline"
                                    method="post"
                                    action="<?= $baseUrl ?>/disciplinas/excluir"
                                    onsubmit="return confirm('Deseja excluir esta disciplina?')"
                                >
                                    <input type="hidden" name="id" value="<?= (int) $disciplina["id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$disciplinas): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="3">
                                Nenhuma disciplina encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
