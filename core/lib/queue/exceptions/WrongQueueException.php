<?php
namespace Queue\Exceptions;

class WrongQueueException extends \Exception {
    protected $message = "Requested queue not found";
    protected $code = 500;
}