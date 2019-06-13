<?php
namespace Queue\Queues;
use Queue\Overflow\Overflow;

class Redis implements Queue {
    public $name;
    public $conn;
    public $count;
    public $tasks;
    public $overflow;
    public $pushCount = 0;
    public $popCount = 0;

    public function __construct($name, $tasks, $redis_host, $redis_port, $overflow = null) {
        $this->tasks = $tasks;
        $this->conn = new \Redis();
        $this->conn->connect($redis_host, $redis_port);
        $this->name = $name;
        $this->count = $this->getLen();
        if (!$overflow){
            $this->overflow = new Overflow();
        } else {
            $this->overflow = $overflow;
        }
    }

    public function getLen() {
        return $this->conn->lLen($this->getName());
    }

    public function getName(){
        return $this->name;
    }

    public function push($taskData) {
        $result = $this->fpush($taskData);
        if (!$this->overflow->check($this) or !$result){
            $this->overflow->invokeCallback();
            $this->overflow->resolveOverflow($this, $taskData);
        }
        return $result;
    }

    public function fpush($taskData) {
        $this->pushCount++;
        return $this->conn->rPush($this->getName(), json_encode($taskData));
    }

    public function pop() {
        $this->popCount++;
        $result = $this->conn->lPop($this->getName());
        if ($result){
            $result = json_decode($result, true);
        }
        return $result;
    }

    public function stats() {
        $this->count = $this->getLen();
        return ['queue_num' => $this->count];
    }

    public function destroy() {
        $this->conn->delete($this->name);
    }
}