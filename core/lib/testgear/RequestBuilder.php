<?php

namespace Testgear;

use Testgear\Mock\Request;

// TODO: Files handling
class RequestBuilder {

    protected $path;
    protected $method;
    protected $body;
    protected $acceptRequestBody = ['POST', 'PUT'];

    public $request;

    protected $headers = [
        'User-Agent'      => 'Testgear/1.0',
        'Accept'          => '*/*',
        'Cache-Control'   => 'no-cache',
        'Host'            => 'localhost',
        'Connection'      => 'keep-alive',
        'Accept-Encoding' => 'gzip, deflate'
    ];

    public function __construct(string $path, string $method) {
        $this->path = $path;
        $this->method = $method;
        $this->request = new Request();
    }

    public function addHeaders(array $headers) {
        $this->headers = $headers + $this->headers;
    }

    /**
     * @param array|string $body
     */
    public function addBody($body) {
        $this->body = $body;
    }

    public function build() {
        $this->prepareRequest();
        return $this->request;
    }

    protected function prepareRequest() {
        $this->request->server['request_method'] = strtoupper($this->method);
        $this->fillServerTime();
        $this->parseGetQueryString();
        $this->parseHeaders();

        // Check if request method support body payload
        if (in_array($this->request->server['request_method'], $this->acceptRequestBody) && $this->body) {
            $this->parseRequestBody();
        }
    }

    protected function fillServerTime() {
        $this->request->server['request_time'] = time();
        $this->request->server['request_time_float'] = microtime(true);
        $this->request->server['master_time'] = time();
    }

    protected function parseGetQueryString() {
        $parsedURL = parse_url("http://localhost{$this->path}");

        // Parse to server properties
        $this->request->server['request_uri'] = $parsedURL['path'];
        $this->request->server['path_info'] = $parsedURL['path'];

        if (isset($parsedURL['query'])) {
            $this->request->server['query_string'] = $parsedURL['query'];
            // Parse query to array
            parse_str($parsedURL['query'], $this->request->get);
        }
    }

    protected function parseHeaders() {
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[strtolower($key)] = $value;
        }

        $this->request->header = $this->headers;
    }

    protected function parseRequestBody() {
        if (
            // If content-type header is present..
            isset($this->headers['Content-Type'])
            // ... and set to urlencoded ...
            && ($this->headers['Content-Type'] == 'application/x-www-form-urlencoded')
            // ... and body in a string ...
            && (!is_array($this->body))
        ) {
            // ... parse it!
            parse_str($this->body, $this->request->post);
        } else {
            if (is_array($this->body)) {
                $this->request->post = $this->body;
            } else {
                $this->request->raw = $this->body;
            }

        }
    }

}