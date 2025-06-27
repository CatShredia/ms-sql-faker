<section class="table-show">
    <div class="tables">
        <form method="post" action="/seed?<?= htmlspecialchars($params) ?>">
            <?php
            $connectionClass->renderAllDataTypes($params);
            ?>
            <button type='submit'>Сохранить типы заполнения</button>
        </form>
    </div>
    <div class="list-tables">
        <?php
        $connectionClass->renderDatabaseTables($params);
        ?>

        <a href="/seed?<?= $params ?>" class="seed-button">Заполнить</a>
    </div>
</section>