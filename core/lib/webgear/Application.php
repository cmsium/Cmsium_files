<?php

namespace Webgear;

use Plumber\Plumber;
use Router\Router;

/**
 * Class Application (Singleton)
 *
 * @package Webgear
 */
abstract class Application {

    public $router;
    public $server;
    public $request;
    public $response;
    /**
     * @var \Errors\AppErrorHandler
     */
    public $errorHandler;
    public $appDirectory = 'app';
    public $startupCallbacks = [];

    public static $instance;

    public static function getInstance($router = null) {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static($router);
        return static::$instance;
    }

    public function __construct(Router $router) {
        $this->router = $router;

        // Define callback handler
        $router->defineCallbackHandler(new AppCallbackHandler());

        // Register psr-4 autoloader for app classes
        $this->registerAppClassesLoader($this->appDirectory);
    }

    /**
     * Run startup sequence of callbacks. Coroutines usage is allowed.
     */
    public function startup() {
        foreach ($this->startupCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * Add new callback to startup sequence
     *
     * @param $callback callable
     */
    public function registerStartupCallback($callback) {
        $this->startupCallbacks[] = $callback;
    }

    /**
     * Main handler called by web-server
     */
    public function handle($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->response->isFile = false;

        try {
            // Run pre-business middleware (request callbacks)
            $this->runMiddleware('pre');

            // Run app business logic, generating result
            $result = $this->run();

            // Run post-business middleware (response callbacks)
            $this->runMiddleware('post');
        } catch (\Exception $exception) {
            $result = $this->errorHandler->handle($exception);
        }

        // Finish request-response iteration
        $this->finish($result);
    }

    /**
     * Running business logic (presumably using router)
     *
     * To be implemented by children.
     */
    abstract protected function run();

    /**
     * Finishes the request-response cycle by throwing away a response to web-server
     *
     * To be implemented by children.
     *
     * @param $result mixed Result of business logic
     */
    abstract protected function finish($result);

    protected function runMiddleware($context) {
        $argument = $context === 'pre' ? $this->request : $this->response;

        $plumber = Plumber::getInstance();
        $plumber->runPipeline("webgear.$context", $argument);
    }

    protected function registerAppClassesLoader($directory) {
        $loader = new Autoloader;
        $loader->addNamespace('App', ROOTDIR."/$directory");
        $loader->register();
    }

}