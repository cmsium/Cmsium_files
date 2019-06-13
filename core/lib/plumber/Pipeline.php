<?php

namespace Plumber;

class Pipeline {

    public $pipes = [];

    public function addPipe(callable $callback) {
        $this->pipes[] = $callback;
        return $this;
    }

    public function setPipes(array $pipes) {
        $this->pipes = array_merge($this->pipes, $pipes);
    }

    public function run(...$arguments) {
        foreach ($this->pipes as $pipe) {
            $result = $pipe(...$arguments);
            // If false is returned, "break" the chain
            if ($result === false) {
                break;
            }
        }
        return $this;
    }

}