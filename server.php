<?php
require_once __DIR__.'/boot/loader.php';

$server = new \HttpServer\Server(app());
$server->launch();