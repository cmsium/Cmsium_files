<?php
namespace Router;

class Request{

    public $URL;
    public $method;
    public $args;

    public function __construct(){
        //TODO validate
        $this->URL = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        switch ($this->method){
            case "GET": $this->args = $_GET; break;
            case "POST": $this->args = $_POST; break;
            default: $this->args = [];
        }
    }

    public function getArgs($arg = null){
        if ($arg) {
            if (isset($this->args[$arg]))
                return $this->args[$arg];
            else
                return null;
        } else {
            return $this->args;
        }
    }

    public function getURL(){
        return $this->URL;
    }

    public function getMethod(){
        return $this->method;
    }
}