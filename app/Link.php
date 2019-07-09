<?php
namespace App;

use App\Exceptions\MysqlException;
use App\Exceptions\SwooleAlreadyExistException;
use App\Exceptions\LinkCheckException;
use App\Exceptions\LinkCheckStatusException;
use App\Exceptions\SwooleSaveException;

class Link {
    public $props;
    public $table;
    public $mysql;
    public $conn;

    public function __construct(array $data, $table, $mysql = null) {
        $this->props = $data;
        $this->table = $table;
        $this->mysql = $mysql;
        $this->normalizeDate();
    }

    public function __get($name){
        return $this->props[$name] ?? null;
    }

    public function __set($name, $value){
        $this->props[$name] = $value;
    }

    public function __isset($name){
        return isset($this->props[$name]);
    }

    public function __unset($name){
        unset($this->props[$name]);
    }

    public function normalizeDate() {
        if (!empty($this->expire)) {
            $this->expire = \DateTime::createFromFormat(\DateTime::RFC3339, $this->expire)->format('Y-m-d H:i:s');
        } elseif (isset($this->expire)) {
            unset($this->expire);
        }
    }

    public function makeRead() {
        if ($this->type == "read"){
            return true;
        }
        $this->type = "read";
        $this->swooleSave();
        $this->dbUpdate();
    }

    public function makeReadRollback() {
        $this->type = "upload";
        $this->swooleSaveRollback();
        $this->dbUpdateRollback();
    }

    public function makeUpload() {
        if ($this->type == "upload"){
            return true;
        }
        $this->type = "upload";
        $this->swooleSave();
        $this->dbUpdate();
    }

    public function makeUploadRollback() {
        $this->type = "read";
        $this->swooleSaveRollback();
        $this->dbUpdateRollback();
    }

    public function CheckStatus($status) {
        if (!$this->table->exist($this->hash)){
            //TODO log miss
            if ($this->dbGet() === false){
                throw new LinkCheckException();
            }
        } else {
            //TODO log hit
            $this->swooleGet();
        }
        if ($this->type != $status){
            throw new LinkCheckStatusException();
        }
    }

    public function notExist() {
        if ($this->exist()){
            throw new SwooleAlreadyExistException();
        }
    }

    public function exist() {
        return $this->table->exist($this->hash);
    }

    public function swooleGet() {
        $this->props = $this->table[$this->hash]->value;
    }

    public function swooleSave() {
        $this->table[$this->hash] = $this->props;
        if (!$this->exist()){
            throw new SwooleSaveException();
        }
    }

    public function swooleSaveRollback() {
        $this->swooleDelete();
    }

    public function dbConnect() {
        if (!$this->conn) {
            $this->conn = new \Swoole\Coroutine\MySQL();
            $this->conn->connect($this->mysql);
        }
    }

    public function swooleDelete() {
        $this->table->del($this->hash);
    }

    public function swooleDeleteRollback() {
        $this->swooleSave();
    }

    public function dbDelete(){
        $this->dbConnect();
        $query = "DELETE from links where hash='{$this->hash}';";
        $this->conn->query($query);
    }

    public function dbDeleteRollback() {
        $this->conn->rollback();
    }

    public function delete() {
        $this->swooleDelete();
        if (!$this->temp) {
            $this->dbDelete();
        }
    }

    public function deleteRollback() {
        $this->swooleSaveRollback();
        if (!$this->temp) {
            $this->dbDeleteRollback();
        }
    }
    
    public function dbGet() {
        $this->dbConnect();
        $query = "SELECT * from links where hash='{$this->hash}';";
        $data = $this->conn->query($query);
        if ($data) {
            $this->props = $data[0];
        }
        return $data;
    }

    public function dbSave() {
        if ($this->temp){
            return true;
        }
        $this->dbConnect();
        $this->normalizeDate();
        $query = "INSERT INTO links (".
            implode(', ', array_keys($this->props)).
            ") VALUES (".
            rtrim(str_repeat("?,", count($this->props)), ",").
            ")";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute(array_values($this->props));
        if ($result === false){
            throw new MysqlException($this->conn->error);
        }
        return $result;
    }

    public function dbSaveBegin() {
        $this->dbConnect();
        $this->conn->begin();
    }

    public function dbSaveRollback() {
        $this->conn->rollback();
    }

    public function dbSaveCommit() {
        $this->conn->commit();
    }

    public function dbUpdate() {
        if ($this->temp){
            return true;
        }
        $this->normalizeDate();
        $updates=[];
        foreach ($this->props as $key => $value){
            $updates[] = "$key = ?";
        }
        $this->dbConnect();
        $stmt = $this->conn->prepare('UPDATE links SET '.implode(', ', $updates)." WHERE hash = ?");
        $preps = array_values($this->props);
        $preps[] = $this->props['hash'];
        $result = $stmt->execute($preps);
        if ($result === false){
            throw new MysqlException($this->conn->error);
        }
        return $result;
    }

    public function dbUpdateBegin() {
        $this->dbConnect();
        $this->conn->begin();
    }

    public function dbUpdateRollback() {
        $this->conn->rollback();
    }

    public function dbUpdateCommit() {
        $this->conn->commit();
    }

}