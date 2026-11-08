<h1 class="h3 mb-4">Relatório de Desempenho</h1>
<div class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <form method="get" action="<?= BASE_URL ?>/relatorios" class="d-flex gap-2 align-items-end">
        <div><label for="status" class="form-label">Situação</label>
            <select class="form-select" id="status" name="status">
                <option value="todos" <?= $status === "todos" ? "selected" : "" ?>>Todos</option>
                <option value="aprovados" <?= $status === "aprovados" ? "selected" : "" ?>>Aprovados</option>
                <option value="reprovados" <?= $status === "reprovados" ? "selected" : "" ?>>Reprovados</option>
            </select></div>
        <button class="btn btn-primary" type="submit">Filtrar</button>
    </form>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/relatorios/exportar?status=<?= urlencode($status) ?>">Baixar CSV</a>
</div>
<?php if (!$resultados): ?>
    <div class="alert alert-info">Nenhum resultado encontrado.</div>
<?php else: ?>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Aluno</th><th>Disciplina</th><th>Média</th><th>Média mínima</th><th>Situação</th></tr></thead>
        <tbody><?php foreach ($resultados as $linha): ?><tr>
            <td><?= htmlspecialchars($linha["aluno"], ENT_QUOTES, "UTF-8") ?></td>
            <td><?= htmlspecialchars($linha["disciplina"], ENT_QUOTES, "UTF-8") ?></td>
            <td><?= number_format($linha["media"], 2, ",", ".") ?></td>
            <td><?= number_format($linha["media_minima"], 2, ",", ".") ?></td>
            <td><span class="badge text-bg-<?= $linha["situacao"] === "Aprovado" ? "success" : "secondary" ?>"><?= $linha["situacao"] ?></span></td>
        </tr><?php endforeach; ?></tbody>
    </table></div>
<?php endif; ?>
