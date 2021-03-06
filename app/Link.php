<?php
namespace App;

use App\Exceptions\MysqlException;
use App\Exceptions\SwooleAlreadyExistException;
use App\Exceptions\LinkCheckException;
use App\Exceptions\LinkCheckStatusException;
use App\Exceptions\SwooleSaveException;
use DateTime;

class Link {
    public $props;
    public $table;

    public function __construct(array $data, $table) {
        $this->props = $data;
        $this->table = $table;
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
            if ($this->temp) {
                if ($datetime = \DateTime::createFromFormat(\DateTime::RFC3339, $this->expire)) {
                    $this->expire = $datetime->format('Y-m-d H:i:s');
                }
            } else {
                unset($this->expire);
            }
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

    public function swooleDelete() {
        $this->table->del($this->hash);
    }

    public function swooleDeleteRollback() {
        $this->swooleSave();
    }

    public function dbDelete(){
        db()->delete("DELETE from links where hash='{$this->hash}';");
    }

    public function dbDeleteRollback() {
        db()->rollback();
    }

    public function delete() {
        $this->swooleDelete();
        if (!$this->temp) {
            $this->dbDelete();
        }
    }

    public function tempDelete() {
        if ($this->temp) {
            $this->swooleDelete();
        }
    }

    public function tempDeleteRollback() {
        if ($this->temp) {
            $this->swooleDeleteRollback();
        }
    }

    public function deleteRollback() {
        $this->swooleSaveRollback();
        if (!$this->temp) {
            $this->dbDeleteRollback();
        }
    }
    
    public function dbGet() {
        $data = db()->selectFirst("SELECT * from links where hash='{$this->hash}';");
        if ($data) {
            $this->props = $data;
        }
        return $data;
    }

    public function dbSave() {
        if ($this->temp){
            return true;
        }
        $this->normalizeDate();
        $result = db()->insert("INSERT INTO links (".
            implode(', ', array_keys($this->props)).
            ") VALUES (".
            rtrim(str_repeat("?,", count($this->props)), ",").
            ")",
            array_values($this->props)
        );
        if ($result === false){
            throw new MysqlException();
        }
        return $result;
    }

    public function dbSaveBegin() {
        db()->startTransaction();
    }

    public function dbSaveRollback() {
        db()->rollback();
    }

    public function dbSaveCommit() {
        db()->commit();
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
        $preps = array_values($this->props);
        $preps[] = $this->props['hash'];
        $result = db()->update('UPDATE links SET '.implode(', ', $updates)." WHERE hash = ?", $preps);
        if ($result === false){
            throw new MysqlException();
        }
        return $result;
    }

    public function dbUpdateBegin() {
        db()->startTransaction();
    }

    public function dbUpdateRollback() {
        db()->rollback();
    }

    public function dbUpdateCommit() {
        db()->commit();
    }

    public function cleanExpired($expire) {
        if (isset($this->expire)){
            $link_expire = new DateTime($this->expire);
            if ($expire > $link_expire){
                $this->swooleDelete();
            }
        }
    }

    public function getUploadLink($host) {
        return "http://$host/file/{$this->hash}";
    }

}