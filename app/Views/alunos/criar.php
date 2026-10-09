<?php $baseUrl = BASE_URL; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mb-3">
            <a class="text-decoration-none" href="<?= $baseUrl ?>/alunos">&larr; Voltar para alunos</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Novo aluno</h1>

                <?php require __DIR__ . "/_form.php"; ?>
            </div>
        </div>
    </div>
</div>
