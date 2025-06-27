<section class="table-show">
    <div class="tables">
        <?php
        $connectionClass->renderAllDataTypes($params);
        ?>
    </div>
    <div class="list-tables">
        <?php
        $connectionClass->renderDatabaseTables($params);
        ?>

        <a href="/seed?<?= $params ?>" class="seed-button">Заполнить</a>
    </div>
</section>