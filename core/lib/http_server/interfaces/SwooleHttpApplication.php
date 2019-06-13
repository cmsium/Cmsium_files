<?php

namespace HttpServer;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface SwooleHttpApplication {

    function handle(Request $request, Response $response);

}