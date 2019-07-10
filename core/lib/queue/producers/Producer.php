<?php
namespace Queue\Producers;
use Queue\Exceptions\ExchangeConnectError;
use Queue\Exceptions\PushErrorException;

class Producer{
    public const DIRECT = 0;
    public const FANOUT = 1;
    public const TOPIC = 2;

    public $host;
    public $port;
    public $client;
    public $headers;

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
        $this->getHeaders();
    }

    public function connect() {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$this->client->connect($this->host, $this->port)){
            throw new ExchangeConnectError();
        }
    }

    public function getHeaders() {
        $this->connect();
        $this->client->send(json_encode(['headers']));
        $this->headers = json_decode($this->client->recv(), true);
        $this->close();
    }

    public function send($headers, array $data, $mode = null) {
        if (is_array($headers)){
            $headers = $this->formHeaders($headers);
        } else {
            $headers = [$headers];
        }
        $this->connect();
        $message = ['push', $headers, $data];
        if ($mode){
            $message[] = $mode;
        }
        $this->client->send(json_encode($message));
        $result = json_decode($this->client->recv(), true);
        if ($result === false){
            throw new PushErrorException();
        }
        $this->close();
    }

    public function stop() {
        $this->connect();
        $this->client->send(json_encode(['stop']));
        $this->close();
    }

    public function close() {
        $this->client->close();
    }

    public function formHeaders($headers) {
        if (!(empty($this->headers))){
            $result = [];
            foreach ($headers as $key => $value){
                if (isset($this->headers[$key]))
                    $result[$this->headers[$key]] = $value;
            }
            return $result;
        }
        return $headers;
    }
}