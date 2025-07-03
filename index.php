<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . "/db/Connection.php";
require_once __DIR__ . "/db/FakerSeeder.php";
require_once __DIR__ . "/Render.php";
require_once __DIR__ . "/route.php";

use SystemDb\Connection;
use Db\FakerSeeder;

$fakerSeeder = new FakerSeeder();
$connectionClass = new Connection($fakerSeeder);

handleRequest($connectionClass);