<?php
namespace App;


use App\Exceptions\ControllerConnectError;

class ControllerClient {
    public $url;
    public $port;
    public $timeout;
    public $client;

    public function __construct($url, $port, $timeout) {
        $this->url = $url;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function connect() {
        $this->client = new \Swoole\Coroutine\Http\Client($this->url, $this->port);
    }

    public function deleteFile($file) {
        $this->connect();
        $this->client->setMethod("DELETE");
        $this->client->set(['timeout' => $this->timeout]);
        $result = $this->client->execute("/file/{$file->file_id}");
        if ($result === false){
            throw new ControllerConnectError();
        }
        $this->client->close();
    }
}