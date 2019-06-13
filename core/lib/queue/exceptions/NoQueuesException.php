<?php
namespace Queue\Exceptions;

class NoQueuesException extends \Exception {
    protected $message = "No active queues";
    protected $code = 500;
}