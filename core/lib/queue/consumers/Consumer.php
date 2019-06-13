<?php
namespace Queue\Consumers;

use Queue\Exceptions\ExchangeConnectError;
use Queue\Exceptions\WrongQueueException;
use Queue\Queues\QueueClient;


class Consumer{

    public $queues = [];
    public $host;
    public $port;
    public $manager;

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
        $this->manager = new \swoole_client(SWOOLE_SOCK_TCP);
    }

    public function managerConnect() {
        if (!$this->manager->connect($this->host, $this->port)){
            throw new ExchangeConnectError();
        }
    }

    public function subscribe(string $queue) {
        $this->managerConnect();
        $this->manager->send(json_encode(['queue', $queue]));
        $queue_info = json_decode($this->manager->recv(), true);
        if (!$queue_info){
            throw new WrongQueueException();
        }
        $this->queues[$queue] = new QueueClient($queue_info['name'], $queue_info['host'], $queue_info['port']);
        $this->manager->close();
    }

    public function on(string $queue, $callback, $fetchTime = null) {
        $queue = $this->getQueue($queue);
        if (!$fetchTime){
            $fetchTime = 1;
        }
        \swoole_timer_tick($fetchTime , [$this, 'invoke'], [$queue, $callback]);
    }

    public function invoke($tid, $args) {
        try{
            $queue = $args[0];
            $callback = $args[1];
            $data = $queue->pop();
            if ($data) {
                try {
                    $callback($data);
                } catch (\Exception $ex){
                    //TODO logs
                    var_dump($ex->getMessage());
                    $this->returnTask($queue, $data);
                }
            }
        } catch (\Exception $e) {
            //TODO logs
            var_dump($e->getMessage());
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