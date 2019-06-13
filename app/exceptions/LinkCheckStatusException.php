<?php
namespace App\Exceptions;

class LinkCheckStatusException extends \Exception {
    protected $message = "hash not found";
    protected $code = 404;
}