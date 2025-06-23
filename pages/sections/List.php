<section class="show-tables">
    <?php
    $dbList = $connectionClass->getListDatabases();

    echo "<ul>";
    foreach ($dbList as $item) {
        echo "<a href=\"/dba?dbName=" . urlencode($item) . "\">";
        echo "<li>" . htmlspecialchars($item) . "</li>";
        echo "</a>";
    }
    echo "</ul>";
    ?>
</section>