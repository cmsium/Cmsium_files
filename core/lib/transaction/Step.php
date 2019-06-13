<?php
namespace Transaction;


class Step {

    public $done;
    public $args=[];
    public $class;
    public $method;

    public function __construct($done, $class, $method, $args) {
        $this->done = $done;
        $this->args = $args;
        $this->class = $class;
        $this->method = $method;
    }

    public function getRollbackMethod() {
        return $this->method."Rollback";
    }

    public function getBeginMethod() {
        return $this->method."Begin";
    }

    public function getCommitMethod() {
        return $this->method."Commit";
    }

}