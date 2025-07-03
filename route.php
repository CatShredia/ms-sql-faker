<?php

function handleRequest($connectionClass)
{
    $renderClass = new Render();

    // получаем url
    $uri = $_SERVER['REQUEST_URI'];
    // получаем параметры
    $param_uri = substr($uri, strpos($uri, '?') + 1);
    // убираем параметры
    $uri = strtok($uri, '?');
    // убираем первый /
    $uri = trim($uri, '/');

    if (empty($uri)) {
        $uri = '/';
    }

    // отдельно POST отдельно GET
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        switch (true) {
            case $uri === '/':
                $renderClass->MainRender($connectionClass, "List");
                break;
            case $uri === 'dba':
                $renderClass->MainRenderParams($connectionClass, "Dba", $param_uri);
                break;
            case $uri === 'seed':
                $renderClass->MainRenderParams($connectionClass, "Seed", $param_uri);
                break;
            case $uri === 'drop':
                $renderClass->MainRenderParams($connectionClass, "Drop", $param_uri);
                break;
            default:
                break;
        }
    } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
        switch (true) {
            case $uri === 'seed':
                $data = $_POST['fill_type'] ?? [];
                $param_uri = explode("=", $param_uri)[1];;

                $filePath = __DIR__ . '/resources/format_jsons/' . $param_uri . '.json';

                echo $filePath;
                // Сохраняем в JSON
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            case 'seeding':
                $connectionClass->SeedDB($param_uri);
                break;
            case 'drop-db':
                $dbName = $_GET['dbName'] ?? null;

                if ($dbName) {
                    $connectionClass->truncateAllTables($dbName);
                    header("Location: /dba?dbName=$dbName");
                    exit();
                } else {
                    echo "Не указано имя БД";
                }
                break;
        }
    }
}