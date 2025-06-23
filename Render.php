<?php

class Render
{
    public function MainRender($connectionClass, $section)
    {
        include __DIR__ . "/pages/MainPage.php";
    }

    public function MainRenderParams($connectionClass, $section, $params)
    {
        include __DIR__ . "/pages/MainPage.php";
    }
}