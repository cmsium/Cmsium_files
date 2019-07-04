<?php


namespace App;


use App\Exceptions\FileGetException;
use App\Exceptions\MysqlException;
use App\Exceptions\SwooleSaveException;
use Files\drivers\Swoole;

class File {
    public $table;
    public $data=[];
    public $mysql;
    public $conn;
    public $link;
    public $queue;
    public $file = null;
    public $send = false;
    public $memory_limit = 4*1024*1024;
    
    public function __construct($table, $mysql = null, $link = null, $queue = null) {
        $this->table = $table;
        if ($mysql){
            $this->mysql = $mysql;
        }
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
        $file = (new \File($this->path))->with(["driver" => new Swoole(), "name" => $this->name]);
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
            $this->swooleGet();
            if ($this->is_delete == 1){
                throw new FileGetException();
            }
        } else {
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

    public function dbConnect() {
        if (!$this->conn) {
            $this->conn = new \Swoole\Coroutine\MySQL();
            $this->conn->connect($this->mysql);
        }
    }

    public function dbUpdate() {
        $this->dbConnect();
        $stmt = $this->conn->prepare("UPDATE files SET file_id=?, path=?, name=?, is_delete=? WHERE file_id = ?");
        $preps = array_values([$this->file_id, $this->path, $this->name, $this->is_delete]);
        $preps[] = $this->file_id;
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

    public function dbGet() {
        $this->dbConnect();
        $stmt = $this->conn->prepare("SELECT * FROM files WHERE file_id=? and is_delete=0;");
        $result = $stmt->execute([$this->file_id]);
        if ($result === false){
            throw new MysqlException($this->conn->error);
        }
        return $result;
    }


    public function dbSave() {
        $this->dbConnect();
        $stmt = $this->conn->prepare("INSERT INTO files (file_id, path, name, is_delete) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$this->file_id, $this->path, $this->name, $this->is_delete]);
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

    public function __get($name){
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

}