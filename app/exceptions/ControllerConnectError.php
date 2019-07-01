<?php
namespace App\Exceptions;

class ControllerConnectError extends \Exception {
    protected $message = "Can not connect to controller server";
    protected $code = 500;
}