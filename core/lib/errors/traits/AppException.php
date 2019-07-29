<?php
namespace Errors\Traits;

trait AppException {
    public function handle($app) {
        $app->setStatusCode($this->code);
    }

    public function get() {
        return $this->message();
    }
}