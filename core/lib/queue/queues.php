<?php
namespace Queue;

define('ROOTDIR', __DIR__);
include ROOTDIR.'/ManifestParser.php';

$parser = new ManifestParser();
$queues = $parser->getQueues();
$opt = "-d";
if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'stop':
            $opt = "-s";
            break;
        case 'kill':
            $opt = "-k";
            break;
        case 'start':
            $opt = "-d";
            break;
    }
}
foreach ($queues as $name => $queue){
    $str = "php QueueServer.php -n $name $opt";
    `$str`;
}