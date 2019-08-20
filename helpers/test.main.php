<?php

function app() {
    return \Webgear\Swoole\Application::getInstance();;
}

function view($template) {
    return \Presenter\PageBuilder::getInstance()->build($template);
}

function db() {
    $app = \Webgear\Swoole\Application::getInstance();
    return $app->db;
}

//TODO refactor after testgear files support
function files(){
    return app()->test_files;
}