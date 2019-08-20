<?php

namespace Testgear;

use Testgear\Mock\Response;
use Webgear\Application;

trait CanRequestToApp {

    /**
     * @var \Webgear\Application
     */
    protected $application;
    protected $methods = [
        'GET', 'POST', 'PUT'
    ];

    public function setApplication(Application $app) {
        $this->application = $app;
    }

    public function get(string $path, array $headers = []): Response {
        return $this->handle($path, 'GET', $headers, null);
    }

    public function delete(string $path, array $headers = []): Response {
        return $this->handle($path, 'DELETE', $headers, null);
    }

    public function post(string $path, $data, array $headers = []): Response {
        return $this->handle($path, 'POST', $headers, $data);
    }

    public function put(string $path, $data, array $headers = []): Response {
        return $this->handle($path, 'PUT', $headers, $data);
    }

    public function getJson(string $path, array $headers = []): Response {
        $bakedHeaders = $this->addJsonContentType($headers);
        return $this->handle($path, 'GET', $bakedHeaders, null);
    }

    public function deleteJson(string $path, array $headers = []): Response {
        $bakedHeaders = $this->addJsonContentType($headers);
        return $this->handle($path, 'DELETE', $bakedHeaders, null);
    }

    public function postJson(string $path, $data, array $headers = []): Response {
        $bakedHeaders = $this->addJsonContentType($headers);
        return $this->handle($path, 'POST', $bakedHeaders, $data);
    }

    public function putJson(string $path, $data, array $headers = []): Response {
        $bakedHeaders = $this->addJsonContentType($headers);
        return $this->handle($path, 'PUT', $bakedHeaders, $data);
    }

    public function handle(string $path, string $method, $headers, $data): Response {
        $requestBuilder = new RequestBuilder($path, $method);

        if ($headers) {
            $requestBuilder->addHeaders($headers);
        }
        if ($data) {
            $requestBuilder->addBody($data);
        }

        $request = $requestBuilder->build();

        $response = new Response;
        $response->setTestCase($this);

        $this->application->handle($request, $response);
        return $response;
    }

    protected function addJsonContentType(array $headers) {
        $contentType['Accept'] = 'application/json';
        return $headers + $contentType;
    }

}