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

        $database = $_ENV['DB_DATABASE'];
        $uid = $_ENV['DB_USERNAME'];
        $pwd = $_ENV['DB_PASSWORD'];

        return [
            "Database" => $database,
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
}