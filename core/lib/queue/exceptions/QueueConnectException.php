<?php
namespace Queue\Exceptions;

class QueueConnectException extends \Exception {
    protected $message = "Queue connect error";
    protected $code = 500;
}