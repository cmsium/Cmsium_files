<?php
namespace App;


class Status {

    public $status;
    public $storage_path;

    public function __construct($status, $storage_path) {
        $this->status = $status;
        $this->storage_path = $storage_path;
    }

    public function get() {
        $space = disk_free_space($this->storage_path);
        $connections = app()->server->swooleServer->connections->count();
        return ['status' => $this->status, 'space' => $space, 'workload' => $connections];
    }
}