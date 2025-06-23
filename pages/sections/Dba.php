<section class="table-show">
    <div class="tables">
        <?php
        $connectionClass->renderAllTablesData($params);
        ?>
    </div>
    <div class="list-tables">
        <?php
        $connectionClass->renderDatabaseTables($params);
        ?>
    </div>
</section>