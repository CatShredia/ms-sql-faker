<?php

namespace SystemDb;

use Dotenv\Dotenv;

class Connection
{
    private $conn;

    public function __construct()
    {
        $connectionArray = $this->getDotenvEnv();
        $serverName = $this->getServerNameEnv();

        $this->conn = sqlsrv_connect($serverName, $connectionArray);

        if ($this->conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public function getTable()
    {
        $sql = "SELECT * FROM Table_1";

        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Пример: возвращаем данные как массив
        $results = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    public function getListDatabases()
    {
        $sql = "
            SELECT name 
            FROM sys.databases 
            WHERE database_id > 4  
            ORDER BY name;
        ";

        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            die("Ошибка выполнения запроса: " . print_r(sqlsrv_errors(), true));
        }

        $databases = [];

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $databases[] = $row['name'];
        }

        return $databases;
    }

    private function getDotenvEnv()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $uid = $_ENV['DB_USERNAME'];
        $pwd = $_ENV['DB_PASSWORD'];

        return [
            "UID" => $uid,
            "PWD" => $pwd,
            "CharacterSet" => "UTF-8",
            "ReturnDatesAsStrings" => true
        ];
    }

    private function getServerNameEnv()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        return $_ENV['DB_SERVER'];
    }

    public function getConnection()
    {
        return $this->conn;
    }
    public function renderDatabaseTables($dbName)
    {
        // Экранируем имя БД на случай пробелов или спецсимволов
        $safeDbName = explode("=", $dbName)[1]; // Убираем скобки, если есть

        // Переключаемся на нужную БД
        $sqlUseDb = "USE [$safeDbName]";
        $stmtUseDb = sqlsrv_query($this->conn, $sqlUseDb);

        if ($stmtUseDb === false) {
            die("Ошибка подключения к БД: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmtUseDb);

        // Запрос на получение списка таблиц
        $sql = "
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_TYPE = 'BASE TABLE'
        ";

        $stmt = sqlsrv_query($this->conn, $sql);

        if ($stmt === false) {
            die("Ошибка выполнения запроса: " . print_r(sqlsrv_errors(), true));
        }


        echo "<h2>Список таблиц в базе данных <em>" . htmlspecialchars($safeDbName) . "</em></h2>";
        echo "<ul>";

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo "<li>" . htmlspecialchars($row['TABLE_NAME']) . "</li>";
        }

        echo "</ul>";

        sqlsrv_free_stmt($stmt);
    }
}