<?php


namespace App;


use App\Exceptions\FileGetException;
use App\Exceptions\MysqlException;
use App\Exceptions\SwooleSaveException;
use Files\BaseFile;
use Files\drivers\Swoole;

class AppFile {
    public $table;
    public $data=[];
    public $link;
    public $queue;
    public $file = null;
    public $send = false;
    public $memory_limit = 4*1024*1024;
    
    public function __construct($table, $link = null, $queue = null) {
        $this->table = $table;
        if ($link){
            $this->link = $link;
        }
        if ($queue){
            $this->queue = $queue;
        }
        if (!$this->is_delete) {
            $this->is_delete = 0;
        }
    }

    public function createFromData($data) {
        $this->data = $data;
    }

    public function send($app) {
        $file = (new BaseFile($this->path))->with(["driver" => new Swoole(), "name" => $this->name]);
        if ($file->size > $this->memory_limit){
            $this->send = $file->sendChunked($app, $this->memory_limit);
        } else {
            $this->send = $file->send($app);
        }
    }

    public function isSend() {
        return $this->send;
    }

    public function swooleSave() {
        $this->table[$this->file_id] = [
            'file_id' => $this->file_id,
            'path' => $this->path,
            'name' => $this->name,
            'is_delete' => $this->is_delete];
        if (!$this->exist()){
            throw new SwooleSaveException();
        }
    }

    public function swooleSaveRollback() {
        $this->table->del($this->file_id);
    }

    public function swooleGet() {
        $this->data = $this->table[$this->file_id]->value;
    }

    public function exist() {
        return $this->table->exist($this->file_id);
    }


    public function deferredDelete($queue_name = 'files.delete') {
        $this->queue->send($queue_name, ['path' => $this->path]);
    }

    public function createFromLink() {
        $this->file_id = $this->link->file;
    }

    public function upload($manager, $files, $file, $storage) {
        $path = $this->generatePath($storage);
        $manager->upload($files, $path);
        $this->create($manager, $file);
    }

    public function generatePath($storage) {
        $this->id = md5($this->name.microtime(true));
        $path = "{$storage}/".substr($this->id, 0, 2)."/{$this->id}";
        return $path;
    }

    public function uploadRollback() {
        $this->file->delete();
    }

    public function create($manager, $file) {
        $obj = $manager->get($file);
        $this->file = $obj;
        $this->data = array_merge($this->data, (array)$obj);
        $this->is_delete = 0;
    }

    public function get() {
        if ($this->exist()){
            //TODO log hit
            $this->swooleGet();
            if ($this->is_delete == 1){
                throw new FileGetException();
            }
        } else {
            //TODO log miss
            if (!($result = $this->dbGet())){
                throw new FileGetException();
            }
            $this->data = $result[0];
        }
    }

    public function makeDeleted() {
        $this->is_delete = 1;
        $this->swooleSave();
        $this->dbUpdate();
    }

    public function makeDeletedRollback() {
        $this->is_delete = 0;
        $this->swooleSave();
        $this->dbUpdateRollback();
    }

    public function dbUpdate() {
        $preps = array_values([$this->file_id, $this->path, $this->name, $this->is_delete]);
        $preps[] = $this->file_id;
        $result = db()->update("UPDATE files SET file_id=?, path=?, name=?, is_delete=? WHERE file_id = ?", $preps);
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

    public function dbGet() {
        $result = db()->select("SELECT * FROM files WHERE file_id='{$this->file_id}' and is_delete=0;");
        if ($result === false){
            throw new MysqlException();
        }
        return $result;
    }


    public function dbSave() {
        $fields = [$this->file_id, $this->path, $this->name, $this->is_delete];
        $result = db()->insert("INSERT INTO files (file_id, path, name, is_delete) VALUES (?, ?, ?, ?)", $fields);
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

    public function __get($name){
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

}