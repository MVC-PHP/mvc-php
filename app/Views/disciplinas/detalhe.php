<?php $baseUrl = BASE_URL; ?>

<div class="mb-3">
    <a class="text-decoration-none" href="<?= $baseUrl ?>/disciplinas">&larr; Voltar para disciplinas</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1"><?= htmlspecialchars($disciplina["nome"]) ?></h1>
        <p class="text-secondary mb-0">
            Média mínima para aprovação:
            <strong><?= number_format((float) $disciplina["media_minima"], 1, ",", ".") ?></strong>
        </p>
    </div>
    <a class="btn btn-outline-primary" href="<?= $baseUrl ?>/disciplinas/editar?id=<?= (int) $disciplina["id"] ?>">
        Editar
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Alunos e médias</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Média</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                        <tr>
                            <td><?= htmlspecialchars($aluno["nome"]) ?></td>
                            <td><?= number_format((float) $aluno["media"], 1, ",", ".") ?></td>
                            <td>
                                <span class="badge text-bg-<?= $aluno["situacao"] === "Aprovado" ? "success" : "danger" ?>">
                                    <?= htmlspecialchars($aluno["situacao"]) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$alunos): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="3">
                                Nenhum aluno com notas nesta disciplina.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
