<?php $baseUrl = BASE_URL; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Notas</h1>
        <p class="text-secondary mb-0">Gerencie as notas dos alunos.</p>
    </div>
    <a class="btn btn-primary" href="<?= $baseUrl ?>/notas/criar">Nova nota</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Disciplina</th>
                        <th>Nota</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $nota): ?>
                        <tr>
                            <td><?= htmlspecialchars($nota["aluno"]["nome"] ?? "Aluno não encontrado") ?></td>
                            <td><?= htmlspecialchars($nota["disciplina"]["nome"] ?? "Disciplina não encontrada") ?></td>
                            <td><?= number_format((float) $nota["valor"], 1, ",", ".") ?></td>
                            <td class="text-end text-nowrap">
                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?= $baseUrl ?>/notas/editar?id=<?= (int) $nota["id"] ?>"
                                >
                                    Editar
                                </a>
                                <form
                                    class="d-inline"
                                    method="post"
                                    action="<?= $baseUrl ?>/notas/excluir"
                                    onsubmit="return confirm('Deseja excluir esta nota?')"
                                >
                                    <input type="hidden" name="id" value="<?= (int) $nota["id"] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$notas): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="4">
                                Nenhuma nota cadastrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
