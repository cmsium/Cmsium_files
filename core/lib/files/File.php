<?php

/**
 * Class File. Implements a number of methods for working with files.
 */
class File {

    public $id;
    public $path;
    public $name;
    public $type;
    public $size;

    public $driver;

    public function __construct($path = null) {
        if ($path) {
            $this->path = $path;
            $this->size = filesize($path);
        }
        return $this;
    }

    public function with($data) {
        foreach ($data as $key => $value){
            $this->$key = $value;
        }
        return $this;
    }

    public function generateId() {
        $this->id = md5($this->name.microtime(true));
        return $this->id;
    }

    public function read(...$args) {
        return $this->driver->read($this,...$args);
    }

    public function exists() {
        return $this->driver->exists($this);
    }

    public function write($content,...$args) {
        return $this->driver->write($this,$content,...$args);
    }


    public function delete() {
        return $this->driver->delete($this);
    }

    public function send(...$args) {
        return $this->driver->send($this,...$args);
    }

    public function sendChunked(...$args) {
        return $this->driver->sendChunked($this,...$args);
    }

}