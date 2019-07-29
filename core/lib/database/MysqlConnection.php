<?php
namespace DB;

use Config\ConfigManager;
use DB\Exceptions\DBConnectionCloseException;
use DB\Exceptions\DBConnectionException;
use DB\Exceptions\RunQueryException;
use DB\Exceptions\TransactionException;
use DB\Exceptions\UnsupportedDataTypeException;
use mysqli;

class MysqlConnection {

    use RelationalQueries;

    protected $conn;

    public function __construct($useDb = true) {
        $config = ConfigManager::module('db');

        $host = $config->get('servername');
        $port = (int)$config->get('port');
        $dbname = $useDb ? $config->get('dbname') : null;
        $username = $config->get('username');
        $password = $config->get('password');

        $conn = new mysqli($host, $username, $password, $dbname, $port);
        if ($conn->connect_errno) {
            throw new DBConnectionException();
        }
        $conn->set_charset('utf8');
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
        $result = $conn->query($query);
        if ($result) {
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return true;
        } else {
            return false;
        }
    }

    protected function performMultiQuery($query) {
        $conn = $this->conn;
        $result = $conn->multi_query($query);
        if ($result) {
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return true;
        } else {
            return false;
        }
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
            $result = $row->fetch_array(MYSQLI_ASSOC);
            $row->close();
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return $result ? $result : null;
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
        $row = $conn->query($query);
        if ($row) {
            $result = $row->fetch_all(MYSQLI_ASSOC);
            $row->close();
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return $result ? $result : null;
        } else {
            return false;
        }
    }

    /**
     * Performs prepared query with an array of params.
     * Type sensitive!
     *
     * @param string $query Query statement
     * @param array $params Enumerated array of binding params
     * @return bool Status
     * @throws UnsupportedDataTypeException
     */
    protected function performPreparedQuery($query, array $params) {
        $conn = $this->conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $types = $this->getDataTypes($params);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            return false;
        }
        $stmt->close();
        return true;
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
            return false;
        }
        $types = $this->getDataTypes($params);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            return false;
        }
        $result = $stmt->get_result();
        $final_result = [];
        while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
            $final_result[] = $data;
        }
        $stmt->close();
        return $final_result;
    }

    /**
     * Detects prepared statement data type by php data type
     *
     * @param $params
     * @return string Data type
     * @throws UnsupportedDataTypeException
     */
    private function getDataTypes($params) {
        $types = '';
        foreach ($params as $value) {
            switch (gettype($value)) {
                case 'integer':
                    $types .= 'i';
                    break;
                case 'string':
                    $types .= 's';
                    break;
                case 'double':
                    $types .= 'd';
                    break;
                case 'NULL':
                    $types .= 's';
                    break;
                default:
                    throw new UnsupportedDataTypeException;
            }
        }
        return $types;
    }

    /**
     * Sets autocommit db setting
     *
     * @param $mode bool Autocommit mode
     * @throws TransactionException
     */
    public function setAutoCommit($mode) {
        $conn = $this->conn;
        if (!$conn->autocommit($mode)) {
            throw new TransactionException;
        }
    }

    /**
     * Starts a new transaction
     *
     * @param bool $read_only If true, sets transaction to read only mode
     * @throws TransactionException
     */
    public function startTransaction($read_only = false) {
        $conn = $this->conn;
        $param = $read_only ? MYSQLI_TRANS_START_READ_ONLY : MYSQLI_TRANS_START_READ_WRITE;
        if (!$conn->begin_transaction($param)) {
            throw new TransactionException;
        }
    }

    /**
     * Rollbacks a transaction
     */
    public function rollback() {
        $conn = $this->conn;
        if (!$conn->rollback()) {
            throw new TransactionException;
        }
    }

    /**
     * Commits a transaction
     */
    public function commit() {
        $conn = $this->conn;
        if (!$conn->commit()) {
            throw new TransactionException;
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