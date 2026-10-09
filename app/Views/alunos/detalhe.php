<?php $baseUrl = BASE_URL; ?>

<div class="mb-3">
    <a class="text-decoration-none" href="<?= $baseUrl ?>/alunos">&larr; Voltar para alunos</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1"><?= htmlspecialchars($aluno["nome"]) ?></h1>
        <p class="text-secondary mb-0">Notas e médias do aluno em cada disciplina.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= $baseUrl ?>/alunos/editar?id=<?= (int) $aluno["id"] ?>">
        Editar
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Disciplinas e médias</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Notas</th>
                        <th>Média</th>
                        <th>Média mínima</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <tr>
                            <td><?= htmlspecialchars($disciplina["nome"]) ?></td>
                            <td>
                                <?= implode(" · ", array_map(
                                    fn (float $valor): string => number_format($valor, 1, ",", "."),
                                    $disciplina["notas"]
                                )) ?>
                            </td>
                            <td><?= number_format((float) $disciplina["media"], 1, ",", ".") ?></td>
                            <td>
                                <?= $disciplina["media_minima"] !== null
                                    ? number_format((float) $disciplina["media_minima"], 1, ",", ".")
                                    : "-" ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $disciplina["situacao"] === "Aprovado" ? "success" : "danger" ?>">
                                    <?= htmlspecialchars($disciplina["situacao"]) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$disciplinas): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="5">
                                Nenhuma nota lançada para este aluno.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
