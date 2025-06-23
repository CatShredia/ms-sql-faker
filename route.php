<?php

use system\Logger;
use controllers\Controller;
use controllers\PostController;

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
            default:
                break;
        }
    } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    }
}