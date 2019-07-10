<?php

namespace Openapi;
use Openapi\Router\RouterCreator;

class OpenApiGenerator {
    public $configPath = "config.ini";
    public $openapiPath;
    public $masksPath;
    public $routesPath;
    public $controllersPath;

    public $routesNamespace;
    public $validationNamespace;
    public $validationMasksNamespace;

    public $maskCreator;
    public $routerCreator;

    public $openapi;

    public function __construct() {
        $this->openapiPath = $this->getConfig("openapiPath");
        $this->masksPath = $this->getConfig("masksPath");
        $this->routesPath = $this->getConfig("routesPath");
        $this->controllersPath = $this->getConfig("controllersPath");
        $this->validationNamespace = $this->getConfig("validationNamespace");
        $this->validationMasksNamespace = $this->getConfig("masksNamespace");
        $this->routesNamespace= $this->getConfig("routesNamespace");

        $this->maskCreator = new ValidationMaskCreator($this->masksPath, $this->validationMasksNamespace);
        $this->routerCreator = new RouterCreator(
            $this->routesPath, $this->controllersPath,
            $this->routesNamespace, $this->validationNamespace
        );
    }

    public function getConfig($config_name) {
        if (file_exists($this->configPath)) {
            $config = parse_ini_file($this->configPath);
            return $config[$config_name];
        } else {
            die('Config file not found!'.PHP_EOL);
        }
    }

    public function parse() {
        $str = file_get_contents($this->openapiPath);
        if ($str !== null) {
            $this->openapi = json_decode($str);
        } else {
            die("OpenAPI manifest not found!");
        }
    }

    public function save() {
        file_put_contents($this->openapiPath, json_encode($this->openapi,  JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    public function loadControllers() {
        require_once __DIR__ ."/router/Routable.php";
        foreach (glob(__DIR__."/".$this->controllersPath."/*.php") as $filename) {
            require_once $filename;
        }
    }

    public function saveRoutes() {
        $this->loadControllers();
        if (!isset($this->openapi->paths)){
            $this->openapi->paths = new \stdClass();
        }
        $routes = $this->routerCreator->read();
        foreach ($routes as $HTTPmethod => $paths){
            foreach ($paths as $path){
                $this->addPath($HTTPmethod, $path);
            }
        }
        foreach ($this->routerCreator->tags as $tag){
            $this->addTag($tag);
        }
    }

    public function addTag($tag) {
        if (!empty($this->openapi->tags)) {
            foreach ($this->openapi->tags as $otag) {
                if ($otag->name == $tag->name) {
                    return;
                }
            }
        }
        $this->openapi->tags[] = $tag;
    }

    public function addPath($method, $path) {
        $method = strtolower($method);
        $props = $this->openapi->paths->{$path->path}->$method;
        $props->summary = $path->summary;
        $props->description = $path->description;
        $props->operationId = $path->method;
        $props->tags = [];
        $props->tags[] = substr($path->class,0, -10);
    }

    public function saveMasks() {
        $this->maskCreator->loadMasks();
        foreach ($this->routerCreator->routes as $HTTPmethod => $paths){
            foreach ($paths as $path){
                $mask = $this->maskCreator->read($path->method);
                $this->addMask($mask, $path);
            }
        }
    }

    public function addMask($mask, $path) {
        switch ($mask->type){
            case "OpenAPIParameters":
                $method = strtolower($path->HTTPmethod);
                $this->openapi->paths->{$path->path}->$method->parameters = $mask->structure;
                break;
            case "OpenAPIContent":
                $method = strtolower($path->HTTPmethod);
                $contentType = "application/json";
                @$this->openapi->paths->{$path->path}->$method->requestBody->content->$contentType->schema = $mask->structure;
                break;
        }
    }


    public function generateRoutes() {
        foreach ($this->openapi->paths as $pathName => $path){
            foreach (get_object_vars($path) as $method => $props){
                $this->routerCreator->create($method,$pathName, $props->tags[0],$props->operationId, $props->summary, $props->description);
            }
        }
        $this->routerCreator->addTags($this->openapi->tags);
        $this->routerCreator->saveRoutes();
    }

    public function generateControllers($withValidation = false) {
        $this->routerCreator->saveControllers($withValidation);
    }

    public function generateMasks() {
        echo "masks: ".PHP_EOL;
        foreach ($this->openapi->paths as $pathName => $path){
            foreach (get_object_vars($path) as $method => $props){
                if (isset($props->parameters)){
                    $data = json_decode(json_encode($props->parameters), true);
                    $this->maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIParameters");
                }
                if (isset($props->requestBody)){
                    foreach ($props->requestBody->content as $contentType => $value){
                        switch ($contentType){
                            case 'multipart/form-data':
                            case 'application/json':
                                $data = json_decode(json_encode($value->schema), true);
                                if (key_exists('$ref', $data)){
                                    @$schema = end(explode('/',$data['$ref']));
                                    $data = json_decode(json_encode($this->openapi->components->schemas->$schema), true);
                                }
                                $this->maskCreator->create(ucfirst($props->operationId), $data, "OpenAPIContent");
                                break;
                        }
                    }
                }
            }
        }
    }
}