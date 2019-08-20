<?php
require_once __DIR__.'/boot/loader.php';

$server = \HttpServer\Server::getInstance($application);
$server->launch();