<?php $baseUrl = BASE_URL; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Entrar</h1>
                <form method="post" action="<?= $baseUrl ?>/login">
                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input class="form-control" type="email" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="senha">Senha</label>
                        <input class="form-control" type="password" id="senha" name="senha" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>