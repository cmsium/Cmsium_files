<?php
namespace Queue\Queues;
use Queue\Exceptions\QueueConnectException;

class QueueClient implements Queue {

    public $name;
    public $host;
    public $port;
    public $client;

    public function __construct(string $name, string $host, $port) {
        $this->name = $name;
        $this->host = $host;
        $this->port = $port;
    }

    //TODO async way?
    public function connect() {
        $this->client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $result = $this->client->connect($this->host, $this->port);
        if (!$result){
            throw new QueueConnectException();
        }
    }
    

    public function stats() {
        $this->connect();
        $this->client->send(json_encode(['stats']));
        $result = json_decode($this->client->recv(), true);
        $this->close();
        return $result;
    }

    public function pop() {
        $this->connect();
        $this->client->send(json_encode(['pop']));
        $result = json_decode($this->client->recv(), true);
        $this->close();
        return $result;
    }

    public function push($data) {
        $this->apush($data);
        return $this->receive();
    }

    public function apush($data) {
        $this->connect();
        $this->client->send(json_encode(['push', $data]));
    }

    public function receive() {
        $result = json_decode($this->client->recv(), true);
        $this->close();
        return $result;
    }

    public function destroy() {
        $this->connect();
        $this->client->send(json_encode(['destroy']));
        $this->close();
    }

    public function stop() {
        $this->connect();
        $this->client->send(json_encode(['stop']));
        $this->close();
    }

    public function getInfo() {
        return ['name' => $this->name, 'host' => $this->host, 'port' => $this->port];
    }

    public function getName() {
        return $this->name;
    }

    public function close() {
        $this->client->close();
    }

}