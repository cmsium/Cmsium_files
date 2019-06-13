<?php
namespace Queue\Queues;
use Queue\Overflow\Overflow;

class SwooleChannel implements Queue{
    public $name;
    public $tasks;
    public $chan;
    public $overflow;
    public $pushCount = 0;
    public $popCount = 0;

    public function __construct($name, $tasks, $overflow = null) {
        $this->tasks = $tasks;
        $this->chan = new \chan($tasks);
        $this->name = $name;
        if (!$overflow){
            $this->overflow = new Overflow();
        } else {
            $this->overflow = $overflow;
        }
    }

    public function getName(){
        return $this->name;
    }

    public function push($taskData) {
        $this->fpush($taskData);
        if (!$this->overflow->check($this)){
            $this->overflow->invokeCallback();
            $this->overflow->resolveOverflow($this, $taskData);
        }
        return true;
    }

    public function fpush($taskData) {
        $this->pushCount++;
        return $this->chan->push($taskData);
    }

    public function pop() {
        $this->popCount++;
        return $this->chan->pop();
    }

    public function stats() {
        return $this->chan->stats();
    }

    public function destroy() {
        unset($this->chan);
    }
}