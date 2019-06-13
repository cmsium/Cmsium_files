<?php

namespace HttpServer;

use Router\Request;

class SwooleRequest extends Request {

    public function __construct($request) {
        $this->URL = $request->server['request_uri'];
        $this->method = $request->server['request_method'];
        switch ($this->method){
            case "GET": $this->args = $request->get; break;
            case "POST": $this->args = $request->post; break;
            case "PUT": $this->args = $request->post; break;
            case "DELETE": $this->args = $request->get; break;
            case "HEAD": $this->args = $request->get; break;
            default: $this->args = [];
        }
    }

}