<section class="show-tables">
    <?php
    $dbList = $connectionClass->getListDatabases();

    echo "<ul>";
    foreach ($dbList as $item) {
        echo "<li>" . htmlspecialchars($item) . "</li>";
    }
    echo "</ul>";
    ?>
</section>