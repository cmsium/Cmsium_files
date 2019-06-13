<?php
namespace Queue\Exceptions;

class WrongBoundException extends \Exception {
    protected $message = "Bound must be between 0 and 100 %";
    protected $code = 500;
}