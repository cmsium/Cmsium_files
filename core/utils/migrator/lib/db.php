<?php
namespace Migrator\DB;

use Migrator\Files;

/**
 * Библиотека содержит функции для работы с БД: подключение, исполнение запросов
 */

/**
 * Функция подключения к БД с помощью настроек, указанных в файле конфигурации
 *
 * @param bool $useDB
 * @param string $configPath Путь к файлам настроек, по умолчанию определяется в константах defaults.php
 * @return bool|\PDO
 */
function connectDB($useDB = true, $configPath = SETTINGS_PATH) {
    try {
        $configString = 'mysql:host='.Files\getConfig('servername', $configPath);
        $configString .= ';port='.Files\getConfig('port', $configPath).';';
        if ($useDB) {
            $configString .= 'dbname='.Files\getConfig('dbname', $configPath).';';
        }

        $conn = new \PDO($configString,
            Files\getConfig('username', $configPath),
            Files\getConfig('password', $configPath));

        // set the PDO error mode to exception
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(\PDOException $e) {
        return false;
    }
}

/**
 * Функция уничтожает текущее подключение к БД
 *
 * @param $conn
 */
function closeConnection(&$conn) {
    $conn = null;
}

/**
 * Функция подключается к БД и осуществляет изъятие информации о таблицах в БД, указанной в конфигурации
 *
 * @param $conn
 * @return mixed Массив данных с таблицами в БД
 */
function getSchemaTables($conn) {
    $dbname = Files\getConfig('dbname');
    $query = $conn->query("SELECT table_name, column_name FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE table_schema = '$dbname' ORDER BY table_name, ordinal_position");
    return $query->fetchAll();
}

/**
 * Функция подключается к БД и создаёт указанную в настройках БД
 *
 * @param $conn
 * @return bool
 */
function createDB($conn) {
    try {
        $dbname = Files\getConfig('dbname');
        $conn->exec("CREATE SCHEMA IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8;");
        return true;
    } catch (\PDOException $e) {
        return false;
    }
}

/**
 * Функция подключается к БД и уничтожает указанную в настройках БД
 *
 * @param $conn
 * @return bool
 */
function dropDB($conn) {
    try {
        $dbname = Files\getConfig('dbname');
        $conn->exec("DROP SCHEMA IF EXISTS `$dbname`;");
        return true;
    } catch (\PDOException $e) {
        return false;
    }
}

/**
 * Функция исполняет запрос к БД, переданный в параметрах
 *
 * @param $conn
 * @param string $query Запрос в БД
 * @return bool
 */
function runQuery($conn, $query) {
    try {
        $conn->query($query);
        return true;
    } catch(\PDOException $e) {
        return false;
    }
}

/**
 * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива значений
 *
 * @param $conn
 * @param string $query Запрос в БД
 * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
 */
function runQueryFetch($conn, $query) {
    try {
        $sth = $conn->prepare($query);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    } catch(\PDOException $e) {
        return false;
    }
}

/**
 * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива всех значений
 *
 * @param string $query Запрос в БД
 * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
 */
function runQueryFetchAll($conn, $query) {
    try {
        $sth = $conn->prepare($query);
        $sth->execute();
        return $sth->fetchAll();
    } catch(\PDOException $e) {
        return false;
    }
}

/**
 * Procedure calling unified interface
 *
 * @param $conn
 * @param $procedure string Procedure name
 * @param $props array|bool Array of arguments
 * @param string $fetch_mode string Fetch mode
 * @return array|bool Result or false
 */
function callProcedure($conn, $procedure, $props = false, $fetch_mode = 'none') {
    $props = $props ? implode(', ',$props) : '';
    $query = "CALL $procedure($props);";
    switch ($fetch_mode) {
        case 'none':
            return runQuery($conn, $query); break;
        case 'fetch':
            return runQueryFetch($conn, $query); break;
        case 'fetch_all':
            return runQueryFetchAll($conn, $query); break;
        default:
            return false;
    }
}

/**
 * Starts a new transaction
 *
 * @param $conn
 */
function startTransaction($conn) {
    $conn->beginTransaction();
}

/**
 * Rollbacks a transaction
 *
 * @param $conn
 */
function rollback($conn) {
    $conn->rollBack();
}

/**
 * Commits a transaction
 *
 * @param $conn
 */
function commit($conn) {
    $conn->commit();
}
