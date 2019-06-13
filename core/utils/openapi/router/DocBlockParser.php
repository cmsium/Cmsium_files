<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/21/19
 * Time: 5:32 PM
 */

namespace Openapi\Router;


class DocBlockParser {
    public $class;
    public $method;

    public $classReflector;
    public $methodReflector;

    public $classDocBlock;
    public $methodDocBlock;

    public function __construct($class, $method) {
        $this->class = $class;
        $this->method = $method;
        $this->classReflector = new \ReflectionClass($class);
        $this->methodReflector = new \ReflectionMethod($class, $method);
        $this->classDocBlock = $this->classReflector->getDocComment();
        $this->methodDocBlock = $this->methodReflector->getDocComment();
    }

    public function getMethodSummary() {
        preg_match("/@summary (.*)/",$this->methodDocBlock,$summary);
        return $summary[1];
    }

    public function getMethodDescription() {
        preg_match("/@description (.*)/",$this->methodDocBlock,$description);
        return $description[1];
    }

    public function getClassDescription() {
        preg_match("/@description (.*)/",$this->classDocBlock,$description);
        return $description[1];
    }
}