<?php
namespace Router;

use Closure;
use Router\exceptions\CallbackHandlerException;
use Router\exceptions\NotFoundException;
use Router\exceptions\UnsupportedMethodException;

class Router {
    public $routes;
    public $routeClass;
    public $supportedMethods = ['GET','POST','PUT','DELETE','HEAD'];
    
    public $callbackHandler;

    public function __construct($routeClass = Route::class){
        $this->routeClass = $routeClass;
    }

    public function __call($name, $args){
        $name = strtoupper($name);
        if (in_array($name,$this->supportedMethods)){
            $route = $this->assign($name, ...$args);
            return $route;
        } else {
            throw new UnsupportedMethodException();
        }
    }

    public function defineCallbackHandler($handler) {
        if (!($handler instanceof callbackHandler)){
            throw new CallbackHandlerException();
        }
        $this->callbackHandler = $handler;
    }

    public function assign($http_method, $path, $closure, $method = null){
        if ($this->callbackHandler){
            $route = new $this->routeClass($path, $this->callbackHandler);
        } else {
            $route = new $this->routeClass($path);
        }
        $this->routes[$http_method][] = $route;
        $this->attachAction($route,$closure,$method);
        return $route;
    }

    public function attachAction($route, $closure, $method = null){
        if (is_string($method)) {
            $route->attachMethod($closure, $method);
        } elseif ($closure instanceof Closure){
            $route->attachClosure($closure);
        }
    }

    public function route(Request $request){
        foreach ($this->routes[$request->getMethod()] as $route) {
            if ($route->match($request->getURL())) {
                return $route->invoke($request);
            }
        }
        throw new NotFoundException();
    }
}