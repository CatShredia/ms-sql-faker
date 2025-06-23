<?php

namespace SystemDb;

use Dotenv\Dotenv;

class Connection
{

    private static $conn;

    public static function SetConnection()
    {
        $connectionArray = Connection::GetDotenvEnv();
        $serverName = Connection::GetServerNameEnv();

        Connection::$conn = sqlsrv_connect($serverName, $connectionArray);

        if (Connection::$conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public static function GetTable()
    {
        $sql = "SELECT * FROM Table_1";

        $stmt = sqlsrv_query(Connection::$conn, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public static function GetListTables()
    {
        $sql = "
            SELECT name 
            FROM sys.databases 
            WHERE database_id > 4  -- Исключаем системные БД
            ORDER BY name;
        ";

        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            die("Ошибка выполнения запроса: " . print_r(sqlsrv_errors(), true));
        }

        // Формируем HTML <ul>
        $html = "<ul>";

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $html .= "<li>" . htmlspecialchars($row['name']) . "</li>";
        }

        $html .= "</ul>";
    }

    private static function GetDotenvEnv()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $database = $_ENV['DB_DATABASE'];
        $uid = $_ENV['DB_USERNAME'];
        $pwd = $_ENV['DB_PASSWORD'];

        $connectionInfo = array(
            "Database" => $database,
            "UID" => $uid,
            "PWD" => $pwd,
            "CharacterSet" => "UTF-8",
            "ReturnDatesAsStrings" => true
        );

        return $connectionInfo;
    }

    private static function GetServerNameEnv()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        $serverName = $_ENV['DB_SERVER'];

        return $serverName;
    }
}