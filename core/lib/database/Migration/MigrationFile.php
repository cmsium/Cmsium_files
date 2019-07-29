<?php

namespace DB\Migration;

use Config\ConfigManager;
use DB\Interfaces\MigrationStorageDriver;

class MigrationFile implements MigrationStorageDriver {

    protected $config;
    public $path;

    public function __construct() {
        $this->config = ConfigManager::module('db');
        $this->path = ROOTDIR.$this->config->get('history_path');
    }

    public function read(): array {
        if (file_exists($this->path)) {
            $file = file_get_contents($this->path);
            $contents = explode(PHP_EOL, $file);
            return $contents;
        } else {
            return [];
        }
    }

    public function write($version) {
        preg_match('/^.+\/(.+)$/', $this->path);
        if (file_exists($this->path) && trim(file_get_contents($this->path))) {
            file_put_contents($this->path, PHP_EOL.$version, FILE_APPEND);
        } else {
            file_put_contents($this->path, $version);
        }
    }

    public function clear() {
        preg_match('/^.+\/(.+)$/', $this->path);
        if (file_exists($this->path)) {
            file_put_contents($this->path, '');
        }
    }

    public function deleteLast() {
        preg_match('/^.+\/(.+)$/', $this->path);
        $migrations = $this->read();
        if (count($migrations) > 1) {
            array_pop($migrations);
            $str = implode(PHP_EOL, $migrations);
            file_put_contents($this->path, $str);
        } else {
            $this->clear();
        }
    }


}