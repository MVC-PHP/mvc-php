<?php $baseUrl = BASE_URL; ?>

<form method="post" action="<?= $baseUrl ?>/disciplinas/salvar">
    <?php if (isset($disciplina["id"])): ?>
        <input type="hidden" name="id" value="<?= (int) $disciplina["id"] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="nome">Nome da disciplina</label>
        <input
            class="form-control"
            id="nome"
            name="nome"
            type="text"
            maxlength="100"
            value="<?= htmlspecialchars((string) $disciplina["nome"]) ?>"
            placeholder="Ex.: Programação PHP"
            required
        >
    </div>

    <div class="mb-4">
        <label class="form-label" for="media_minima">Média mínima para aprovação</label>
        <input
            class="form-control"
            id="media_minima"
            name="media_minima"
            type="number"
            min="0"
            max="10"
            step="0.1"
            value="<?= htmlspecialchars((string) $disciplina["media_minima"]) ?>"
            required
        >
    </div>

    <button class="btn btn-primary" type="submit">Salvar</button>
    <a class="btn btn-light" href="<?= $baseUrl ?>/disciplinas">Cancelar</a>
</form>
