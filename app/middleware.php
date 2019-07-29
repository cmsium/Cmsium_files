<?php

//Api middleware
$api->addPipe(function () {
    app()->setHeader('Content-Type', 'application/json');
});