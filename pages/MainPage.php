<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="resources/style.css">
</head>

<body>
    <header class="header">
        <div class="header_container container">
            <nav class="header__nav">
                <ul>
                    <li><a href="#">Главная</a></li>
                    <li><a href="#">Просмотр</a></li>
                    <li><a href="#">Заполнение</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="main_container container">
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
        </div>
    </main>
    <footer class="footer">d</footer>
</body>

</html>