<?php
namespace Validation\exceptions;
use Exception;

class DataFormatException extends Exception {
    protected $message = "Wrong data format";
}