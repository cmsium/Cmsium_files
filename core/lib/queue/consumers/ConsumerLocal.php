<?php
namespace Queue\Consumers;

use Queue\Exceptions\WrongQueueException;
use Queue\Queues\QueueManager;

class ConsumerLocal {

    public $queues = [];

    public function subscribe(QueueManager $manager, $queue) {
        $this->queues[$queue] = $manager->getQueue($queue);
    }

    public function on($queue, $callback, $fetchTime = null) {
        $queue = $this->getQueue($queue);
        if (!$fetchTime){
            $fetchTime = 1;
        }
        \swoole_timer_tick($fetchTime , [$this, 'invoke'], [$queue, $callback]);
    }

    public function invoke($tid, $args) {
        $queue = $args[0];
        $callback = $args[1];
        $num = $queue->stats()['queue_num'];
        if ($num !== 0){
            $data = $queue->pop();
            try {
                $callback($data);
            } catch (\Exception $e) {
                $this->returnTask($queue, $data);
            }
        }
    }

    public function returnTask($queue, $data) {
        $queue->push($data);
    }

    public function getQueue($name) {
        if (!key_exists($name, $this->queues)){
            throw new WrongQueueException();
        }
        return $this->queues[$name];
    }

}