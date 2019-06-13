<?php
namespace App\Exceptions;

class SwooleAlreadyExistException extends \Exception {
    protected $message = "Link is already exist error";
    protected $code = 403;
}