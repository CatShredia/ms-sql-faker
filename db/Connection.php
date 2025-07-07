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

    // Получаем данные из env
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

    // Получаем данные из env
    private function getServerNameEnv()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        return $_ENV['DB_SERVER'];
    }

    // Получает список всех пользовательских баз данных на сервере
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

    // Получаем подключение
    public function getConnection()
    {
        return $this->conn;
    }

    // Получает список таблиц из указанной базы данных
    private function getDatabaseTables(string $dbName): array
    {
        $safeDbName = explode("=", $dbName)[1];

        $sqlUseDb = "USE [$safeDbName]";
        $stmtUseDb = sqlsrv_query($this->conn, $sqlUseDb);
        if ($stmtUseDb === false) {
            throw new \RuntimeException("Ошибка подключения к БД: " . print_r(sqlsrv_errors(), true));
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
            throw new \RuntimeException("Ошибка выполнения запроса: " . print_r(sqlsrv_errors(), true));
        }

        $tables = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $tables[] = $row['TABLE_NAME'];
        }

        sqlsrv_free_stmt($stmt);

        return $tables;
    }

    // Выводит HTML-список всех таблиц в указанной базе данных.
    public function renderDatabaseTables(string $dbName): void
    {
        try {
            $tables = $this->getDatabaseTables($dbName);
            $safeDbName = explode("=", $dbName)[1];

            echo "<h2>Список таблиц в базе данных <em>" . htmlspecialchars($safeDbName) . "</em></h2>";
            echo "<ul>";

            foreach ($tables as $table) {
                echo "<li><a href=\"#" . htmlspecialchars($table) . "\">" . htmlspecialchars($table) . "</a></li>";
            }

            echo "</ul>";
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Произошла ошибка: " . $e->getMessage() . "</p>";
        }
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
            $sqlData = "SELECT  * FROM $safeTableName";
            $stmtData = sqlsrv_query($this->conn, $sqlData);

            if ($stmtData === false) {
                $errors = sqlsrv_errors();

                echo "<p style='color:red;'>Ошибка выполнения запроса для таблицы $tableName:</p>";
                echo "<pre>";
                print_r($errors); // Выводим все ошибки
                echo "</pre>";

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
                    echo "<td>" . htmlspecialchars($value !== null ? (string) $value : '') . "</td>";
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
        $sqlUseDb = "USE [$safeDbName]";
        $stmtUseDb = sqlsrv_query($this->conn, $sqlUseDb);
        if ($stmtUseDb === false) {
            die("Ошибка подключения к БД: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmtUseDb);

        echo "<h2>Типы данных из всех таблиц базы <em>" . htmlspecialchars($safeDbName) . "</em></h2>";

        $sqlTables = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
        $stmtTables = sqlsrv_query($this->conn, $sqlTables);
        if ($stmtTables === false) {
            die("Ошибка получения списка таблиц: " . print_r(sqlsrv_errors(), true));
        }

        $fillTypes = $this->fakerSeeder->getAvailableFillTypes();

        // Путь к JSON-файлу
        $filePath = dirname(__DIR__) . "/resources/format_jsons/" . $safeDbName . ".json";

        // Загружаем сохранённые типы заполнения и количество записей (если есть)
        $savedConfig = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $savedConfig = json_decode($jsonContent, true) ?: [];
        }

        // Форма для отправки выбранных типов
        echo "<form method='POST' action='/save-fill-types'>";
        echo "<input type='hidden' name='dbName' value='" . htmlspecialchars($dbName) . "'>";

        while ($rowTable = sqlsrv_fetch_array($stmtTables, SQLSRV_FETCH_ASSOC)) {
            $tableName = $rowTable['TABLE_NAME'];
            echo "<input type='text' readonly disabled id=\"$tableName\" value=\"$tableName\"></input>";

            // Получаем информацию о PK/FK
            $keyConstraints = $this->getKeyConstraints($tableName);

            $sqlColumns = "
            SELECT COLUMN_NAME, DATA_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$tableName'
        ";
            $stmtColumns = sqlsrv_query($this->conn, $sqlColumns);
            if ($stmtColumns === false) {
                echo "<p style='color:red;'>Ошибка получения структуры таблицы $tableName</p>";
                continue;
            }

            // Поле для ввода количества записей
            $count = isset($savedConfig[$tableName]['_record_count']) ? (int) $savedConfig[$tableName]['_record_count'] : 10;
            echo "<label>Количество записей: 
                <input type='number' name='fill_type[{$tableName}][_record_count]' value='$count' min='1' style='width: 60px;' />
              </label><br><br>";

            echo "<table border='1' cellpadding='5' cellspacing='0' style='margin-bottom: 20px; border-collapse: collapse;'>";
            echo "<tr style='background-color: #f2f2f2;'>
                <th>Поле</th>
                <th>Тип данных</th>
                <th>Пример Заполнения</th>
                <th>Тип заполнения</th>
              </tr>";

            while ($column = sqlsrv_fetch_array($stmtColumns, SQLSRV_FETCH_ASSOC)) {
                $name = htmlspecialchars($column['COLUMN_NAME']);
                $dataType = htmlspecialchars($column['DATA_TYPE']);

                // Проверяем, является ли поле PK или FK
                $keyType = $keyConstraints[$name] ?? null;

                // Получаем сохранённый тип заполнения
                $fillType = $savedConfig[$tableName][$name] ?? $this->fakerSeeder->getFillType($dataType);

                // Генерируем пример на основе сохранённого типа
                $exampleSeed = $this->fakerSeeder->getDataFromFillType($fillType);

                echo "<tr>
                    <td>$name</td>
                    <td>$dataType</td>
                    <td><span class='example-seed'>" . htmlspecialchars((string) $exampleSeed) . "</span></td>
                    <td>";

                if ($keyType === 'PK') {
                    echo "<input readonly name=\"fill_type[{$tableName}][{$name}]\" value='PK'></input>";
                } elseif ($keyType === 'FK') {
                    echo "<input readonly name=\"fill_type[{$tableName}][{$name}]\" value='FK'></input>";
                } else {
                    echo "<select name=\"fill_type[{$tableName}][{$name}]\" class=\"fill-type-select\">";
                    foreach ($fillTypes as $key => $label) {
                        $selected = ($key === $fillType) ? 'selected' : '';
                        $exampleValue = $this->fakerSeeder->getDataFromFillType($key);
                        echo "<option value=\"$key\" data-example=\"" . htmlspecialchars($exampleValue) . "\" $selected>$label</option>";
                    }
                    echo "</select>";
                }

                echo "</td></tr>";
            }

            echo "</table>";
            sqlsrv_free_stmt($stmtColumns);
        }

        echo "</form>";

        sqlsrv_free_stmt($stmtTables);

        // JS для обновления примера при выборе типа
        echo <<<HTML
<script>
document.querySelectorAll('.fill-type-select').forEach(select => {
    select.addEventListener('change', () => {
        const example = select.options[select.selectedIndex].dataset.example;
        select.closest('tr').querySelector('.example-seed').textContent = example;
    });
});
</script>
HTML;
    }

    public function truncateAllTables(string $dbName): void
    {
        // Переключаемся на нужную БД
        $useDbSql = "USE [$dbName]";
        $stmtUseDb = sqlsrv_query($this->conn, $useDbSql);
        if ($stmtUseDb === false) {
            throw new \Exception("Ошибка переключения на БД: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmtUseDb);

        // Получаем список таблиц
        $tables = [];
        $sqlTables = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
        $stmt = sqlsrv_query($this->conn, $sqlTables);
        if ($stmt === false) {
            throw new \Exception("Ошибка получения списка таблиц: " . print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $tables[] = $row['TABLE_NAME'];
        }
        sqlsrv_free_stmt($stmt);

        // Отключаем проверку внешних ключей
        sqlsrv_query($this->conn, "EXEC sp_MSforeachtable 'ALTER TABLE ? NOCHECK CONSTRAINT ALL'");

        // Очищаем таблицы по одной
        foreach ($tables as $table) {
            $quotedTable = "[" . str_replace("]", "]]", $table) . "]";
            sqlsrv_query($this->conn, "DELETE FROM $quotedTable"); // TRUNCATE требует отключения FK
        }

        // Включаем обратно внешние ключи
        sqlsrv_query($this->conn, "EXEC sp_MSforeachtable 'ALTER TABLE ? WITH CHECK CHECK CONSTRAINT ALL'");

        // Сброс автоинкремента
        foreach ($tables as $table) {
            $quotedTable = "[" . str_replace("]", "]]", $table) . "]";
            sqlsrv_query($this->conn, "DBCC CHECKIDENT ('$quotedTable', RESEED, 0)");
        }
    }

    public function getKeyConstraints(string $tableName): array
    {
        $sql = "
        SELECT 
            COLUMN_NAME,
            CONSTRAINT_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE 
            TABLE_NAME = '$tableName'
    ";

        $stmt = sqlsrv_query($this->conn, $sql);
        if ($stmt === false) {
            return [];
        }

        $constraints = [];

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $columnName = $row['COLUMN_NAME'];
            $constraintName = $row['CONSTRAINT_NAME'];

            // Определяем тип ограничения
            if (str_contains($constraintName, 'PK')) {
                $constraints[$columnName] = 'PK';
            } elseif (str_contains($constraintName, 'FK')) {
                $constraints[$columnName] = 'FK';
            }
        }

        sqlsrv_free_stmt($stmt);

        return $constraints;
    }

    public function SeedDB($dbName, $data)
    {
        print_r($data);
        echo "<br>";

        // foreach ($data as $item_1) {
        //     print_r($item_1);
        //     echo "<tab>";
        //     echo "<hr>";
        //     foreach ($item_1 as $item_2) {
        //         print_r($item_2);
        //         echo "<br>";
        //     }
        // }

        foreach ($data as $item_1) {

            $count = $item_1["_record_count"];
            unset($item_1["_record_count"]);

            foreach ($item_1 as $key => $type) {
                $insertData = array();
                echo $key . " " . $type;
                echo "<br>";
                for ($i = 0; $i < $count; $i++) {
                    if ($type == "PK") {
                        $insertData["ID"] = $i;
                    } else {
                        $insertData[$key] = $this->fakerSeeder->getDataFromFillType($type);
                    }
                    // echo "insert - " . $insertData[$key];
                    print_r($insertData);
                    echo "<br>";
                }
            }
            echo "<hr>";
        }

        // Проходим по каждой таблице из $data
        foreach ($data as $tableName => $tableStructure) {
            $startTime = microtime(true); // Засекаем время

            $count = $tableStructure['_record_count'] ?? 10;
            unset($tableStructure['_record_count']); // убираем служебное поле

            // Собираем поля
            $columns = [];
            $types = [];

            foreach ($tableStructure as $column => $type) {
                $columns[] = $column;
                $types[] = $type;
            }

            // Подготавливаем SQL-запрос
            $columnsWithBrackets = array_map(fn($col) => "[$col]", $columns);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));

            $sql = "INSERT INTO [$dbName].dbo.[$tableName] (" . implode(', ', $columnsWithBrackets) . ") VALUES ($placeholders)";

            echo "<h3>Вставка в таблицу: <strong>$tableName</strong></h3>";
            echo "<p><strong>Количество записей:</strong> $count</p>";
            echo "<ul>";
            foreach ($tableStructure as $column => $type) {
                echo "<li><strong>$column</strong> — тип: $type</li>";
            }
            echo "</ul>";

            echo "<p><strong>SQL-запрос:</strong><br><code>" . htmlspecialchars($sql) . "</code></p>";

            // Генерируем данные и вставляем в БД
            for ($i = 0; $i < $count; $i++) {
                $params = [];

                foreach ($tableStructure as $column => $type) {
                    if ($type === 'PK') {
                        $params[] = $i;
                    } elseif ($type === 'FK') {
                        $params[] = rand(1, 9); // можно сделать динамический выбор существующего ID
                    } else {
                        // Предполагается, что $this->fakerSeeder умеет обрабатывать типы
                        $params[] = $this->fakerSeeder->getDataFromFillType($type);
                    }
                }

                // Для отладки выводим параметры
                echo "<p><strong>Параметры:</strong> ";
                print_r($params);
                echo "</p>";

                // Выполняем запрос
                $stmt = sqlsrv_query($this->conn, $sql, $params);

                if ($stmt === false) {
                    echo "<p style='color:red;'><strong>Ошибка при вставке в таблицу [$tableName]</strong></p>";
                    echo "<pre>";
                    print_r(sqlsrv_errors());
                    echo "</pre>";
                    die("Прервано на итерации $i");
                }
            }

            $executionTime = round(microtime(true) - $startTime, 4);
            echo "<p style='color:green;'>✅ Таблица <strong>$tableName</strong> успешно заполнена за $executionTime сек.</p>";
            echo "<hr>";
        }
    }
}