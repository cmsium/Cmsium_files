<?php
namespace Queue\Overflow;
use Queue\Exceptions\WrongBoundException;

class OverflowBound extends Overflow {
    public $bound;

    public function __construct($bound, $mode = self::OVERFLOW_ERROR) {
        if ($bound <= 0 or $bound > 100){
            throw new WrongBoundException();
        }
        $this->bound = $bound;
        $this->mode = $mode;
    }

    public function check($queue) {
        return $queue->stats()['queue_num'] <= (int)($queue->tasks * $this->bound / 100);
    }

}