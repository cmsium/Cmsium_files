<?php

namespace DB;

use Config\ConfigManager;
use DB\Exceptions\DBConnectionCloseException;
use DB\Exceptions\DBConnectionException;
use DB\Exceptions\RunQueryException;
use DB\Exceptions\TransactionException;
use DB\Exceptions\UnsupportedDataTypeException;
use Swoole\Coroutine\MySQL;

/**
 * Class SwooleMysqlConnection
 */
class SwooleMysqlConnection {

    use RelationalQueries;

    public $conn;

    public function __construct($useDb = true) {
        $config = ConfigManager::module('db');

        $connectionConfig = [
            'host' => $config->get('servername'),
            'port' => (int)$config->get('port'),
            'user' => $config->get('username'),
            'password' => $config->get('password')
        ];

        if ($useDb) {
            $connectionConfig['database'] = $config->get('dbname');
        }

        $conn = new MySQL;
        if (!$conn->connect($connectionConfig)) {
            throw new DBConnectionException();
        }

        $this->conn = $conn;
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах
     *
     * @param string $query Запрос в БД
     * @return bool
     */
    protected function performQuery($query) {
        $conn = $this->conn;
        return $conn->query($query) ? true : false;
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива значений
     *
     * @param string $query Запрос в БД
     * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
     */
    protected function performQueryFetch($query) {
        $conn = $this->conn;
        $row = $conn->query($query);
        if ($row) {
            return array_shift($row);
        } else {
            return false;
        }
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива всех значений
     *
     * @param string $query Запрос в БД
     * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
     */
    protected function performQueryFetchAll($query) {
        $conn = $this->conn;
        return $conn->query($query);
    }

    /**
     * Performs prepared query with an array of params.
     * Type sensitive!
     *
     * @param string $query Query statement
     * @param array $params Enumerated array of binding params
     * @return bool Status
     * @throws RunQueryException
     * @throws UnsupportedDataTypeException
     */
    protected function performPreparedQuery($query, array $params) {
        $conn = $this->conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new RunQueryException('Could not prepare query!');
        }
        return $stmt->execute($params);
    }

    /**
     * Performs prepared query with an array of params.
     * Type sensitive!
     *
     * @param string $query Query statement
     * @param array $params Enumerated array of binding params
     * @return bool|array Status
     * @throws UnsupportedDataTypeException
     */
    protected function performPreparedQueryFetchAll($query, array $params) {
        $conn = $this->conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new RunQueryException('Could not prepare query!');
        }
        if (!$stmt->execute($params)) {
            throw new RunQueryException('Could not execute prepared query!');
        }
        return $stmt->fetchAll();
    }

    /**
     * Starts a new transaction
     *
     * @param bool $read_only If true, sets transaction to read only mode
     * @throws TransactionException
     */
    public function startTransaction() {
        $conn = $this->conn;
        if (!$conn->begin()) {
            throw new TransactionException('Begin transaction failed.');
        }
    }

    /**
     * Rollbacks a transaction
     */
    public function rollback() {
        $conn = $this->conn;
        if (!$conn->rollback()) {
            throw new TransactionException('Rollback failed.');
        }
    }

    /**
     * Commits a transaction
     */
    public function commit() {
        $conn = $this->conn;
        if (!$conn->commit()) {
            throw new TransactionException('Commit failed.');
        }
    }

    /**
     * Procedure calling unified interface
     *
     * @param $procedure string Procedure name
     * @param $props array|bool Array of arguments
     * @param string $fetch_mode string Fetch mode
     * @return array|bool Result or false
     * @throws RunQueryException
     */
    public function callProcedure($procedure, $props = false, $fetch_mode = 'none') {
        $props = $props ? implode(', ',$props) : '';
        $query = "CALL $procedure($props);";
        switch ($fetch_mode) {
            case 'none':
                return $this->performQuery($query); break;
            case 'fetch':
                return $this->performQueryFetch($query); break;
            case 'fetch_all':
                return $this->performQueryFetchAll($query); break;
            default:
                throw new RunQueryException($query);
        }
    }

    /**
     * Destroys the object and kills connection
     */
    public function __destruct() {
        if ($this->conn) {
            $conn = $this->conn;
            if (!$conn->close()) {
                throw new DBConnectionCloseException;
            }
        }
    }

}