<?php

// автозагрузка файлов из директорий
spl_autoload_register(function ($class_name) {
    // меняем \ на /
    $class_name = str_replace('\\', '/', $class_name);
    $class_name = __DIR__ . "/" . $class_name . '.php';
    include  $class_name;
});

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . "/db/Connection.php";

use SystemDb\Connection;

$connectionClass = new Connection();

$connectionClass->getConnection();

$renderClass = new Render();

$renderClass->MainRender($connectionClass);