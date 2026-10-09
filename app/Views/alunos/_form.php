<?php $baseUrl = BASE_URL; ?>

<form method="post" action="<?= $baseUrl ?>/alunos/salvar">
    <?php if (isset($aluno["id"])): ?>
        <input type="hidden" name="id" value="<?= (int) $aluno["id"] ?>">
    <?php endif; ?>

    <div class="mb-4">
        <label class="form-label" for="nome">Nome do aluno</label>
        <input
            class="form-control"
            id="nome"
            name="nome"
            type="text"
            maxlength="100"
            value="<?= htmlspecialchars((string) $aluno["nome"]) ?>"
            placeholder="Ex.: Maria Souza"
            required
        >
    </div>

    <button class="btn btn-primary" type="submit">Salvar</button>
    <a class="btn btn-light" href="<?= $baseUrl ?>/alunos">Cancelar</a>
</form>
