<p>Lista de Categorias </p>
<ul>
    <?php foreach($categorias as $c): ?>
        <li>
            <?= $c["nome"] ?>
        </li>
    <?php endforeach; ?>
</ul>