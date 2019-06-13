<?php
namespace Transaction;
use Transaction\Exceptions\WrongClassException;

class Transaction {
    public $classes=[];
    public $current_class;
    public $steps=[];

    public function __construct($classes) {
        if (is_array($classes)){
            $this->classes = $classes;
        } else {
            $this->classes = [$classes];
            $this->current_class = 0;
        }
    }

    public function __get($name) {
        if (key_exists($name, $this->classes)){
            $this->current_class = $name;
            return $this;
        } else {
            throw new WrongClassException();
        }
    }

    public function __call($method, $args) {
        $this->steps[] = new Step(false, $this->current_class, $method, $args);
        return $this;
    }

    private function rollback() {
        foreach ($this->steps as $step){
            if ($step->done) {
                $obj = $this->classes[$step->class];
                $method = $step->getRollbackMethod();
                if (method_exists($obj, $method)) {
                    $obj->$method(...$step->args);
                }
            }
        }
    }

    private function begin(){
        foreach ($this->steps as $step){
            $obj = $this->classes[$step->class];
            $method = $step->getBeginMethod();
            if (method_exists($obj, $method)) {
                $obj->$method(...$step->args);
            }
        }
    }

    private function end(){
        foreach ($this->steps as $step){
            if ($step->done) {
                $obj = $this->classes[$step->class];
                $method = $step->getCommitMethod();
                if (method_exists($obj, $method)) {
                    $obj->$method(...$step->args);
                }
            }
        }
    }

    private function invoke() {
        foreach ($this->steps as $step) {
            $obj = $this->classes[$step->class];
            $method = $step->method;
            $obj->$method(...$step->args);
            $step->done = true;
        }
    }

    public function commit() {
        $this->begin();
        try {
            //TODO async?
            $this->invoke();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
        $this->end();
    }
}