<?php

namespace Plumber;

/**
 * Class Plumber (Singleton, factory)
 *
 * @package Plumber
 */
class Plumber {

    /**
     * Main pipelines array.
     *
     * Every pipeline should have a name to call. Example structure:
     * [
     *  'foo' => obj#Pipeline,
     *  ...
     * ]
     *
     * @var array
     */
    public $pipelines = [];

    public static $instance;

    public static function getInstance() : self {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static;
        return static::$instance;
    }

    public function buildPipeline($name) {
        $pipeline = new Pipeline();
        $this->pipelines[$name] = $pipeline;
        return $pipeline;
    }

    public function getPipeline($name) {
        return $this->pipelines[$name];
    }

    public function runPipeline($name, ...$arguments) {
        $pipeline = $this->pipelines[$name];
        $pipeline->run(...$arguments);
        return $pipeline;
    }

}