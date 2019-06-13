<?php
namespace Router\exceptions;
use Exception;

class TooFewArgumentsException extends Exception{
    protected $message = "Too few arguments";
}