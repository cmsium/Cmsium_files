<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 4:40 PM
 */

namespace Openapi\Router;

class RouteCreator extends Route{
    public $summary;
    public $description;
    public $HTTPmethod;
    public $classDescription;

    public function __construct($routName, $HTTPmethod) {
        parent::__construct($routName);
        $this->HTTPmethod = $HTTPmethod;
    }

    public function attachMethodMeta($summary, $description) {
        $this->summary = $summary;
        $this->description = $description;
    }

    public function attachClassMeta($description) {
        $this->classDescription = $description;
    }


    public function getString(){
        return "\$router->$this->HTTPmethod(\"{$this->path}\", \"".ucfirst($this->class)."Controller\", \"{$this->method}\");".PHP_EOL;
    }
}