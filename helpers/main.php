<?php

function app() {
    return \HttpServer\Server::getInstance()->currentApp();
}

function view($template) {
    return \Presenter\PageBuilder::getInstance()->build($template);
}

function db() {
    return \HttpServer\Server::getInstance()->currentApp()->db;
}

function files(){
    return app()->request->files;
}