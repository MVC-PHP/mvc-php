<?php $baseUrl = BASE_URL; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mb-3">
            <a class="text-decoration-none" href="<?= $baseUrl ?>/notas">&larr; Voltar para notas</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Editar nota</h1>

                <?php if (!$alunos || !$disciplinas): ?>
                    <div class="alert alert-warning" role="alert">
                        É necessário ter pelo menos um aluno e uma disciplina cadastrados.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $baseUrl ?>/notas/salvar">
                    <input type="hidden" name="id" value="<?= (int) $nota["id"] ?>">

                    <div class="mb-3">
                        <label class="form-label" for="aluno_id">Aluno</label>
                        <select class="form-select" id="aluno_id" name="aluno_id" required>
                            <option value="">Selecione um aluno</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option
                                    value="<?= (int) $aluno["id"] ?>"
                                    <?= (int) $aluno["id"] === (int) $nota["aluno_id"] ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($aluno["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="disciplina_id">Disciplina</label>
                        <select class="form-select" id="disciplina_id" name="disciplina_id" required>
                            <option value="">Selecione uma disciplina</option>
                            <?php foreach ($disciplinas as $disciplina): ?>
                                <option
                                    value="<?= (int) $disciplina["id"] ?>"
                                    <?= (int) $disciplina["id"] === (int) $nota["disciplina_id"] ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($disciplina["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="valor">Nota</label>
                        <input
                            class="form-control"
                            id="valor"
                            name="valor"
                            type="number"
                            min="0"
                            max="10"
                            step="0.1"
                            value="<?= htmlspecialchars((string) $nota["valor"]) ?>"
                            required
                        >
                    </div>

                    <button
                        class="btn btn-primary"
                        type="submit"
                        <?= !$alunos || !$disciplinas ? "disabled" : "" ?>
                    >
                        Salvar
                    </button>
                    <a class="btn btn-light" href="<?= $baseUrl ?>/notas">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
