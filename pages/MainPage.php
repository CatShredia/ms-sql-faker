<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="resources/style.css">
</head>

<body>
    <?php include __DIR__ . "/includes/Header.php" ?>

    <main class="main">
        <div class="main_container container">
            <?php include __DIR__ . "/sections/" . $section . ".php" ?>
        </div>
    </main>

    <?php include __DIR__ . "/includes/Footer.php" ?>
</body>

</html>