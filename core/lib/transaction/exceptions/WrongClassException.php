<?php
namespace Transaction\Exceptions;

class WrongClassException extends \Exception {
    protected $message = "Wrong transaction class";
}