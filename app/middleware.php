<?php

//Api middleware
use DB\MysqlConnection;
use DB\SwooleMysqlConnection;

// Pre-run callbacks
$pre->addPipe(function($request) use ($env) {
    if ($env == 'test') {
        app()->db = new MysqlConnection();

        //Create Controller client
        $app_config = \Config\ConfigManager::module('app');
        $url = $app_config->get('controller_url');
        $port = $app_config->get('controller_port');
        $timeout = $app_config->get('controller_timeout');
        $client = new \App\ControllerClient($url, $port, $timeout);
        app()->controller_client = $client;

        //Create Queue Exchange client
        $config = Config\ConfigManager::module('queue');
        $ex_host = $config->get('host');
        $ex_port = $config->get('port');
        $client = new \Queue\Producers\Producer($ex_host, $ex_port);
        app()->queue_client = $client;

    } else {
        app()->db = new SwooleMysqlConnection();
    }
});

// Post-run callbacks
$post->addPipe(function($response) {
    // Implement
});

$api->addPipe(function () {
    app()->setHeader('Content-Type', 'application/json');
});
