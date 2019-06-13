<?php

namespace DB\Exceptions;

use Exception;
use Throwable;

class RunQueryException extends Exception {

    public $query = 'undefined';
    protected $message = "Could not perform query.";

    public function __construct($query = null, string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->query = $query ?? $this->query;
        $this->message = "Could not perform query: {$this->query}";
    }

}