<?php
namespace App\Exceptions;

class LinkCheckException extends \Exception {
    protected $message = "No hash found error";
    protected $code = 404;
}