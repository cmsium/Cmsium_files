<?php
namespace App\Exceptions;

class SwooleSaveException extends \Exception {
    protected $message = "Swoole table save error";
    protected $code = 500;
}