<div class="p-4 p-md-5 bg-white border rounded-3 shadow-sm text-center">
    <h1 class="display-6 fw-semibold mb-3">Ops, algo deu errado</h1>
    <p class="lead text-secondary">
        <?= htmlspecialchars($mensagemErro ?? "Ocorreu um erro inesperado. Tente novamente.") ?>
    </p>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/">Voltar ao início</a>
</div>
