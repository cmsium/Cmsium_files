<?php
namespace Queue\Queues;
use Queue\Overflow\Overflow;

class SwooleTable implements Queue {
    public $types = [
        'string' => \swoole_table::TYPE_STRING,
        'int' => \swoole_table::TYPE_INT
    ];

    public $name;
    public $tasks;
    public $table;
    public $head = 0;
    public $tail = 0;
    public $overflow;
    public $pushCount = 0;
    public $popCount = 0;

    public function __construct($name, $tasks, $task_structure, $overflow = null) {
        $this->tasks = $tasks;
        //TODO make it normal way
        $table_size = bindec(str_pad(1, strlen(decbin((int) $this->tasks - 1)), 0)) * 2;
        $this->table = new \Swoole\Table($table_size*2);
        foreach ($task_structure as $tname => $value){
            switch ($value['type']){
                case 'string': $this->table->column($tname, $this->types[$value['type']], $value['size']); break;
                case 'int': $this->table->column($tname, $this->types[$value['type']]); break;
            }
        }
        $this->table->create();
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
        $result = $this->fpush($taskData);
        if (!$this->overflow->check($this) or !$result){
            $this->overflow->invokeCallback();
            $this->overflow->resolveOverflow($this, $taskData);
        }
        return $result;
    }

    public function fpush($taskData) {
        $this->pushCount++;
        @$this->table->set($this->tail, $taskData);
        if (!$this->table->exist($this->tail)){
            return false;
        }
        $this->tail++;
        return true;
    }

    public function pop() {
        $this->popCount++;
        if (!$this->table->exist($this->head)){
            return false;
        }
        $result = $this->table[$this->head];
        $this->table->del($this->head);
        $this->head++;
        return $result->value;
    }

    public function stats() {
        return ['queue_num' => $this->table->count(), 'head' => $this->head, 'tail' => $this->tail];
    }

    public function destroy() {
        $this->table->destroy();
    }
}