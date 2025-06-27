<section class="show-tables">
    <?php
    if ($params == "drop") {
        $dbList = $connectionClass->getListDatabases();

        echo "<ul>";
        foreach ($dbList as $item) {
            echo "<a href=\"/drop?dbName=" . urlencode($item) . "\">";
            echo "<li>" . htmlspecialchars($item) . "</li>";
            echo "</a>";
        }
        echo "</ul>";
    } else {
        $params = explode("=", $params)[1];

        echo "<p class=\"delete-title\">Вы уверены, что хотите сбросить все таблицы " . $params . "?</p>";
    }

    ?>

    <form action="/drop-db?dbName=<?= htmlspecialchars($params) ?>" method="POST">
        <button type="submit">Да</button>
    </form>
</section>