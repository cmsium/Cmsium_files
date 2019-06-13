<?php
namespace Openapi;


class ValidationMaskCreator {
    public $path;
    public $namespace;
    public $masks=[];

    public function __construct($savePath, $namespace) {
        $this->path = $savePath;
        $this->namespace = $namespace;
    }

    public function create(string $maskName,array $maskData,string $maskType = null) {
        if (!$maskType){
            $maskType = " OpenAPIParameters";
        }
        if (!is_dir($this->path)){
            mkdir($this->path, 0755, true);
        }
        $mask = new ValidationMask($maskName);
        $mask->with($maskData, $maskType, $this->namespace);
        $mask->createString();
        $mask->save($this->path);
        echo "  ".$maskName.PHP_EOL;
        $this->masks[$maskType][$maskName] = $mask;
    }

    public function loadMasks() {
        require_once $this->path.'/Mask.php';
        require_once $this->path.'/DefaultMask.php';
        require_once $this->path.'/OpenAPI.php';
        require_once $this->path.'/OpenAPIContent.php';
        require_once $this->path.'/OpenAPIParameters.php';
        foreach (glob($this->path.'/*.php') as $filename) {
            require_once $filename;
        }
    }

    public function read($maskName) {
        $mask = new ValidationMask($maskName);
        $mask->with(null, null, $this->namespace);
        $mask->read();
        return $mask;
    }

}