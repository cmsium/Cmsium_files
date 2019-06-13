<?php
namespace Queue\Overflow;

class OverflowTrend extends Overflow {
    public $checkTime;
    public $check = true;
    public $timer;
    public $pushCount = 0;
    public $popCount = 0;

    public function __construct($check_time, $mode = self::OVERFLOW_ERROR) {
        $this->checkTime = $check_time;
        $this->mode = $mode;
    }

    public function check($queue) {
        if (!$this->timer){
            $this->timer = swoole_timer_tick($this->checkTime, function ($tid, $queue) {
                $push = $queue->pushCount;
                $pop = $queue->popCount;
                if (($pop - $this->popCount) < ($push - $this->pushCount)){
                    $this->check = false;
                }
                $this->popCount = $pop;
                $this->pushCount = $push;
            }, $queue);
        }
        return $this->check;
    }
}