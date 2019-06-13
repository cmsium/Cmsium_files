<?php

namespace Webgear;

use Plumber\Plumber;
use Router\CallbackHandler;

class AppCallbackHandler implements CallbackHandler {

    public function before() {
        return function (...$args) {
            $plumber = Plumber::getInstance();
            $plumber->runPipeline(array_shift($args), ...$args);
        };
    }

    public function after() {
        return function (...$args) {
            $plumber = Plumber::getInstance();
            $plumber->runPipeline(array_shift($args), ...$args);
        };
    }

}