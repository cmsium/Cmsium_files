<?php
namespace App\Exceptions;

class FileGetException extends \Exception {
    protected $message = "File does not exist or deleted";
    protected $code = 404;
}