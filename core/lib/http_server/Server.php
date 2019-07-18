<?php

namespace HttpServer;

use Config\ConfigManager;
use Exception;
use swoole_http_server;

class Server {

    public $swooleServer;
    public $application;

    private $host;
    private $port;
    private $https;
    private $sslCert;
    private $sslKey;
    private $http2;

    public function __construct(SwooleHttpApplication $application) {
        $this->application = $application;

        $config = ConfigManager::module('http');

        $this->host    = $config->get('host');
        $this->port    = (int)$config->get('port');
        $this->https   = (bool)$config->get('enable_https');
        $this->sslCert = $config->get('ssl_cert_file');
        $this->sslKey  = $config->get('ssl_key_file');
        $this->http2   = (bool)$config->get('enable_http2');

        $this->setStartupScript();
        $this->setRouterWorkflow();
    }

    public function initiateSwooleServer() {
        $this->swooleServer = new swoole_http_server($this->host, $this->port);

        if ($this->https) {
            $this->swooleServer->set([
                'ssl_cert_file' => $this->sslCert,
                'ssl_key_file' => $this->sslKey,
            ]);
        }
        if ($this->http2) {
            $this->swooleServer->set([
                'open_http2_protocol' => $this->http2
            ]);
        }

        return $this;
    }

    public function launch() {
        if (!$this->swooleServer) {
            $this->initiateSwooleServer();
        }
        $this->swooleServer->start();
    }

    private function setStartupScript() {
        if (!$this->swooleServer) {
            $this->initiateSwooleServer();
        }

        $this->swooleServer->on("start", function ($server) {
            try {
                $this->application->startup();

                $protocol = $this->https ? 'https' : 'http';
                echo "HTTP server is started at $protocol://{$this->host}:{$this->port}".PHP_EOL;
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                // TODO: Implement logging
                echo $message.PHP_EOL;
                die();
            }
        });

        return $this;
    }

    private function setRouterWorkflow() {
        if (!$this->swooleServer) {
            $this->initiateSwooleServer();
        }

        $this->swooleServer->on("request", function ($request, $response) {
            try {
                $this->application->server = $this;
                $this->application->handle($request, $response);
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                $response->end($message.PHP_EOL);
            }
        });

        return $this;
    }

    public function __destruct() {
        $this->swooleServer = null;
    }

}