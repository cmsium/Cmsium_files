<?php
namespace Openapi\Router;
use Closure;
use ReflectionFunction;
use ReflectionMethod;
use Router\exceptions\TooFewArgumentsException;

class Route {
    public $path;

    public $closure;

    public $class;
    public $method;

    public $args;

    public $matchString;

    public function __construct(string $path){
        $this->path = $path;
        $this->parsePath();
    }

    public function attachClosure(Closure $closure){
        $this->closure = $closure;
    }

    public function attachMethod($class, $method){
        $this->class = $class;
        $this->method = $method;
    }

    public function parsePath(){
        if (preg_match_all("@{([A-Za-z\d]+)}@U",$this->path,$args)){
            $this->args = array_flip($args[1]);
        }
        $this->matchString = preg_replace("@{[A-Za-z\d]+}@U","([A-Za-z\d]+)", $this->path);
        $this->matchString = str_replace("/","\/",$this->matchString);
        $this->matchString = "@".$this->matchString."@";
        return;
    }

    public function match($path){
        $result = preg_match_all($this->matchString, $path,$args);
        if ($result) {
            array_shift($args);
            if ($args) {
                foreach ($args as $arg) {
                    $flatten_args[] = $arg[0];
                }
                $this->fillArgs($flatten_args);
            }
        }
        return $result;
    }

    public function fillArgs($args){
        foreach ($this->args as $key => $value) {
            $this->args[$key] = $args[$value];
        }

    }

    public function prepareClosureArgs(){
        $arg_count = (new ReflectionFunction($this->closure))->getNumberOfParameters();
        if (count($this->args) < $arg_count) {
            throw new TooFewArgumentsException();
        }
        $this->args = array_slice($this->args,0,$arg_count);
    }

    public function prepareMethodArgs(){
        $arg_count = (new ReflectionMethod($this->class, $this->method))->getNumberOfParameters();
        if (count($this->args) < $arg_count) {
            throw new TooFewArgumentsException();
        }
        $this->args = array_slice($this->args,0,$arg_count);
    }

    public function invokeClosure(){
        if ($this->args) {
            //TODO try catch
            $this->prepareClosureArgs();
            $args = array_values($this->args);
            return ($this->closure)(...$args);
        } else {
            return ($this->closure)();
        }
    }

    public function invokeMethod(Request $request){
        $class_name = "App\\Controllers\\".$this->class;
        $class = new $class_name($request);
        $method = $this->method;
        if ($this->args) {
            //TODO try catch
            $this->prepareMethodArgs();
            $args = array_values($this->args);
            return $class->$method(...$args);
        } else {
            return $class->$method();
        }
    }

    public function invoke(Request $request){
        if ($this->closure) {
            // TODO: pass request object to closure too?
            return $this->invokeClosure();
        } elseif ($this->method){
            return $this->invokeMethod($request);
        }
    }

}