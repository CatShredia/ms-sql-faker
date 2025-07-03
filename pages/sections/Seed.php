<section class="table-show">
    <div class="tables">
        <form method="post" action="/seeding?<?= htmlspecialchars($params) ?>">
            <button type='submit'>Заполнить</button>
            <?php
            $connectionClass->renderAllDataTypes($params);
            ?>
        </form>
    </div>
    <div class="list-tables">
        <?php
        $connectionClass->renderDatabaseTables($params);
        ?>
    </div>
</section>