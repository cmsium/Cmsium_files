<?php
namespace Queue\Overflow;
use Queue\Exceptions\PushErrorException;

class Overflow {
    const OVERFLOW_ERROR = 0;
    const OVERFLOW_POP = 1;

    public $mode;
    public $callback;


    public function __construct($mode = self::OVERFLOW_ERROR) {
        $this->mode = $mode;
    }

    public function check($queue) {
        return true;
    }

    public function registerCallback(callable $callback) {
        $this->callback = $callback;
    }

    public function resolveOverflow($queue, $taskData) {
        switch ($this->mode){
            case self::OVERFLOW_ERROR: throw new PushErrorException(); break;
            case self::OVERFLOW_POP: $queue->pop(); $queue->fpush($taskData); break;
        }
    }

    public function invokeCallback() {
        if ($this->callback){
            $result = ($this->callback)();
            $this->callback = null;
            return $result;
        }
    }
}