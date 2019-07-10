<?php
namespace Queue;

use Queue\Producers\Producer;
use Queue\Queues\QueueClient;
use Queue\Queues\QueueManager;

define('ROOTDIR', __DIR__);
foreach (glob(ROOTDIR."/exceptions/*.php") as $name){
    include $name;
}
foreach (glob(ROOTDIR."/queues/*.php") as $name){
    include $name;
}
include_once ROOTDIR."/tasks/Task.php";
foreach (glob(ROOTDIR."/tasks/*.php") as $name){
    include_once $name;
}
include ROOTDIR."/producers/Producer.php";
include ROOTDIR.'/ManifestParser.php';

$ini = parse_ini_file(ROOTDIR."/config/exchange.ini");
if (isset($ini['mode'])){
    $mode = constant("Queue\Queues\QueueManager::{$ini['mode']}");
} else {
    $mode = null;
}
$manager = new QueueManager($mode);
$parser = new ManifestParser();
$queues = $parser->getQueues();
foreach ($queues as $name => $queue_info){
    $queue = new Queues\QueueClient($name, $queue_info->host, $queue_info->port);
    $manager->registerQueue($queue);
}

$options = getopt("dp:sp:");

if (isset($options['s'])){
    go(function () use ($ini) {
        $client = new Producer($ini['host'], $ini['port']);
        $client->stop();
    });
    die();
}

//TODO normal config
$server = new \swoole_server($ini['host'], $ini['port']);
if (isset($options['d'])){
    $server->set(['daemonize' => 1]);
}

//$server->on('connect', function($server, $fd){
//    TODO logs
//});

$server->on('receive', function($server, $fd, $from_id, $message) use ($manager) {
    try {
        $message = json_decode($message, true);
        $command = $message[0];
        switch ($command) {
            case 'push':
                if (isset($message[3])) {
                    $mode = $message[3];
                } else {
                    $mode = null;
                }
                $manager->route($message[1], $message[2], $mode);
                $server->send($fd, json_encode(true));
                $server->close($fd);
                break;
            case 'headers':
                $server->send($fd, json_encode($manager->getHeaders()));
                $server->close($fd);
                break;
            case 'queue':
                $queue = $message[1];
                $server->send($fd, json_encode($manager->getQueue($queue)->getInfo()));
                $server->close($fd);
                break;
            case 'stop':
                $info = $server->connection_info($fd, $from_id);
                if ($info['remote_ip'] === $server->host){
                    $server->shutdown();
                }
                break;
            default:
                $server->send($fd, "Unknown command");
                $server->close($fd);
        }
    } catch (\Exception $e){
        //TODO logs
        var_dump($e->getMessage());
        $server->send($fd, json_encode(false));
    }

});

//$server->on('close', function($server, $fd){
//    TODO logs
//});

$server->start();