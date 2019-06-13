<?php
namespace Queue\Exceptions;

class PushErrorException extends \Exception {
    protected $message = "Queue push task error";
    protected $code = 500;
}