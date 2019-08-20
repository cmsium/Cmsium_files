<?php

namespace Testgear\Mock;

class Request extends \Swoole\Http\Request {

    public $server = [
        'query_string'       => '',
        'request_method'     => 'GET',
        'request_uri'        => '',
        'path_info'          => '',
        'request_time'       => 1563358806,
        'request_time_float' => 1563358806.5377,
        'server_port'        => 80,
        'remote_port'        => 49914,
        'master_time'        => 1563358806,
        'server_protocol'    => 'HTTP/1.1'
    ];

    public $raw;

    public function rawcontent() {
        return $this->raw;
    }

}