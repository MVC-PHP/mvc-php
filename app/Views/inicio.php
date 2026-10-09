<?php if (!empty($paginaNaoEncontrada)): ?>
    <div class="alert alert-warning" role="alert">
        A página solicitada não foi encontrada.
    </div>
<?php endif; ?>

<div class="p-4 p-md-5 bg-white border rounded-3 shadow-sm">
    <h1 class="display-6 fw-semibold mb-3">Sistema Acadêmico</h1>
    <p class="lead text-secondary mb-0">
        Projeto MVC para cadastro de alunos, disciplinas e notas.
    </p>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title">Alunos</h2>
                <p class="card-text text-secondary">Cadastro e consulta dos alunos.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title">Disciplinas</h2>
                <p class="card-text text-secondary">Cadastro e consulta das disciplinas.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title">Notas</h2>
                <p class="card-text text-secondary">Lançamento e acompanhamento das notas.</p>
            </div>
        </div>
    </div>
</div>
