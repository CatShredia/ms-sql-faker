<?php

namespace SystemDb;

use Db\FakerSeeder;
use Dotenv\Dotenv;

class Connection
{
    private $conn;
    private $fakerSeeder;

    public function __construct($fakerSeeder)
    {
        $this->fakerSeeder = $fakerSeeder;
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
            echo "<li><a href=\"#" . htmlspecialchars($row['TABLE_NAME']) . "\">" . htmlspecialchars($row['TABLE_NAME']) . "</a></li>";
        }

        echo "</ul>";

        sqlsrv_free_stmt($stmt);
    }

    public function renderAllTablesData($dbName)
    {
        // Экранируем имя БД
        $safeDbName = explode("=", $dbName)[1];

        // Переключаемся на нужную БД
        $sqlUseDb = "USE $safeDbName";
        $stmtUseDb = sqlsrv_query($this->conn, $sqlUseDb);

        if ($stmtUseDb === false) {
            die("Ошибка подключения к БД: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmtUseDb);

        // Запрос на получение списка таблиц
        $sqlTables = "
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'
    ";

        $stmtTables = sqlsrv_query($this->conn, $sqlTables);

        if ($stmtTables === false) {
            die("Ошибка получения списка таблиц: " . print_r(sqlsrv_errors(), true));
        }

        echo "<h2>Данные из всех таблиц базы <em>" . htmlspecialchars($dbName) . "</em></h2>";

        // Проходим по всем таблицам
        while ($rowTable = sqlsrv_fetch_array($stmtTables, SQLSRV_FETCH_ASSOC)) {
            $tableName = $rowTable['TABLE_NAME'];
            $safeTableName = "[" . str_replace("]", "]]", $tableName) . "]";

            echo "<h3 id=\"$tableName\">$tableName</h3>";

            // Выбираем данные из таблицы
            $sqlData = "SELECT TOP 5 * FROM $safeTableName";
            $stmtData = sqlsrv_query($this->conn, $sqlData);

            if ($stmtData === false) {
                echo "<p style='color:red;'>Ошибка выполнения запроса для таблицы $tableName</p>";
                continue;
            }

            // Выводим данные в виде таблицы
            echo "<table border='1' cellpadding='5' cellspacing='0' style='margin-bottom: 20px; border-collapse: collapse;'>";

            // Заголовки столбцов
            echo "<tr style='background-color: #f2f2f2;'>";
            foreach (sqlsrv_field_metadata($stmtData) as $field) {
                echo "<th>" . htmlspecialchars($field['Name']) . "</th>";
            }
            echo "</tr>";

            // Данные
            $hasRows = false;
            while ($rowData = sqlsrv_fetch_array($stmtData, SQLSRV_FETCH_ASSOC)) {
                $hasRows = true;
                echo "<tr>";
                foreach ($rowData as $value) {
                    echo "<td>" . htmlspecialchars($value !== null ? (string)$value : '') . "</td>";
                }
                echo "</tr>";
            }

            if (!$hasRows) {
                echo "<tr><td colspan='100'>Нет данных</td></tr>";
            }

            echo "</table>";

            echo "<hr>";

            sqlsrv_free_stmt($stmtData);
        }

        sqlsrv_free_stmt($stmtTables);
    }

    public function renderAllDataTypes($dbName)
    {
        // Экранируем имя БД
        $safeDbName = explode("=", $dbName)[1];

        // Переключаемся на нужную БД
        $sqlUseDb = "USE $safeDbName";
        $stmtUseDb = sqlsrv_query($this->conn, $sqlUseDb);
        if ($stmtUseDb === false) {
            die("Ошибка подключения к БД: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmtUseDb);

        // Запрос на получение списка таблиц
        $sqlTables = "
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'
    ";
        $stmtTables = sqlsrv_query($this->conn, $sqlTables);
        if ($stmtTables === false) {
            die("Ошибка получения списка таблиц: " . print_r(sqlsrv_errors(), true));
        }

        echo "<h2>Типы данных из всех таблиц базы <em>" . htmlspecialchars($dbName) . "</em></h2>";

        // Проходим по всем таблицам
        while ($rowTable = sqlsrv_fetch_array($stmtTables, SQLSRV_FETCH_ASSOC)) {
            $tableName = $rowTable['TABLE_NAME'];
            $safeTableName = "[" . str_replace("]", "]]", $tableName) . "]";

            echo "<h3 id=\"$tableName\">$tableName</h3>";

            // Запрос структуры таблицы через INFORMATION_SCHEMA.COLUMNS
            $sqlColumns = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$tableName'
        ";
            $stmtColumns = sqlsrv_query($this->conn, $sqlColumns);
            if ($stmtColumns === false) {
                echo "<p style='color:red;'>Ошибка получения структуры таблицы $tableName</p>";
                continue;
            }

            // Выводим типы данных в виде таблицы
            echo "<table border='1' cellpadding='5' cellspacing='0' style='margin-bottom: 20px; border-collapse: collapse;'>";
            echo "<tr style='background-color: #f2f2f2;'>
                <th>Поле</th>
                <th>Тип данных</th>
                <th>Размер / Точность</th>
                <th>Пример Заполнения</th>
                <th>Тип заполнения</th>
              </tr>";

            while ($column = sqlsrv_fetch_array($stmtColumns, SQLSRV_FETCH_ASSOC)) {
                $name = htmlspecialchars($column['COLUMN_NAME']);
                $dataType = htmlspecialchars($column['DATA_TYPE']);
                $size = isset($column['CHARACTER_MAXIMUM_LENGTH']) ? htmlspecialchars((string)$column['CHARACTER_MAXIMUM_LENGTH']) : '';
                $precision = isset($column['NUMERIC_PRECISION']) ? htmlspecialchars((string)$column['NUMERIC_PRECISION']) : '';
                $scale = isset($column['NUMERIC_SCALE']) ? htmlspecialchars((string)$column['NUMERIC_SCALE']) : '';

                // Форматируем Размер / Точность
                $sizePrecision = '';
                if ($size !== '') {
                    $sizePrecision .= "Размер: $size";
                }
                if ($precision !== '' && $scale !== '') {
                    $sizePrecision .= ($sizePrecision ? ", " : "") . "Точность: $precision, Масштаб: $scale";
                }

                $exampleSeed = $this->fakerSeeder->GetData($dataType);
                $fillType = $this->fakerSeeder->getFillType($dataType);

                echo "<tr>
                    <td>$name</td>
                    <td>$dataType</td>
                    <td>$sizePrecision</td>
                    <td>" . htmlspecialchars((string)$exampleSeed) . "</td>
                    <td>$fillType</td>
                  </tr>";
            }

            echo "</table>";
            echo "<hr>";
            sqlsrv_free_stmt($stmtColumns);
        }

        sqlsrv_free_stmt($stmtTables);
    }
}
