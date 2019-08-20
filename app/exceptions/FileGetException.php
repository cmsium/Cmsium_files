<?php
namespace App\Exceptions;

use Errors\Traits\AppException;

class FileGetException extends \Exception {
    use AppException;
    protected $message = "File does not exist or deleted";
    protected $code = 404;
}