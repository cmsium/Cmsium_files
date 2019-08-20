<?php

namespace DB\Pool;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class SwooleMysql {

    public $connectionClassName;
    /**
     * @var array
     */
    public $connectionPool = [];
    /**
     * @var Channel
     */
    protected $freeConnectionsChannel;
    /**
     * @var int
     */
    protected $maxConnections;

    public static $instance;

    public static function getInstance(string $connectionClassName, $maxConnections = 10) {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static($connectionClassName, $maxConnections);
        return static::$instance;
    }

    public function __construct(string $connectionClassName, $maxConnections = 10) {
        $this->connectionClassName = $connectionClassName;
        $this->maxConnections = $maxConnections;
        $this->freeConnectionsChannel = new Channel($maxConnections);

        for ($i = 0; $i < $maxConnections; $i++) {
            // Put created connection in the pool
            $connection = new $connectionClassName;
            $connectionId = spl_object_hash($connection);
            $this->connectionPool[$connectionId] = $connection;

            // Send connections in the list of free connections (channel)
            $this->freeConnectionsChannel->push($connectionId);
        }
    }

    public function fetch() {
        // Check if there are any connections in the pool
        if ($this->freeConnectionsChannel->isEmpty()) {
            $requestId = Coroutine::getCid();
            // Channel to get connection id from coroutine
            $channel = new Channel(1);

            // Create coroutine to check if connection becomes available, then resume request
            go(function() use ($channel, $requestId) {
                $conn = false;
                while (!$conn) {
                    $conn = $this->freeConnectionsChannel->pop();
                }
                $channel->push($conn);
                Coroutine::resume($requestId);
            });

            // Suspend current request
            Coroutine::suspend();

            // Get found connection
            $availableConnectionId = $channel->pop();
            $channel->close();
        } else {
            $availableConnectionId = $this->freeConnectionsChannel->pop();
        }

        return $this->resolveConnection($availableConnectionId);
    }

    public function free($connection) {
        $connectionId = spl_object_hash($connection);

        $this->freeConnectionsChannel->push($connectionId);

        return true;
    }

    /**
     * @param $connectionId
     * @return object
     */
    protected function resolveConnection($connectionId) {
        return $this->connectionPool[$connectionId];
    }

}