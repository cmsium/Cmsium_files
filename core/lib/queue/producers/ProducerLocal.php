<?php
namespace Queue\Producers;
use Queue\Exceptions\ExchangeConnectError;
use Queue\Queues\QueueManager;

class ProducerLocal{
    public const DIRECT = 0;
    public const FANOUT = 1;
    public const TOPIC = 2;

    public $manager;
    public $headers;

    public function __construct(QueueManager $manager) {
        $this->client = $manager;
        $this->getHeaders();
    }

    public function connect() {
        if (!$this->client->connect($this->host, $this->port)){
            throw new ExchangeConnectError();
        }
    }

    public function getHeaders() {
        $this->headers = $this->manager->getHeaders();
    }

    public function send($headers, array $data, $mode = null) {
        if (!is_array($headers)){
            $headers = [$headers];
        }
        $this->manager->route($headers, $data, $mode);
    }

}