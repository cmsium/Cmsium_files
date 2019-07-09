<?php
namespace Queue;

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


//TODO normal config
$server = new \swoole_server($ini['host'], $ini['port']);


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
                break;
            case 'headers':
                $server->send($fd, json_encode($manager->getHeaders()));
                break;
            case 'queue':
                $queue = $message[1];
                $server->send($fd, json_encode($manager->getQueue($queue)->getInfo()));
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