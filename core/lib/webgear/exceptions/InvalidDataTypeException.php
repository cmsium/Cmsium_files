<?php

namespace Webgear\Exceptions;

use Exception;

class InvalidDataTypeException extends Exception {

    protected $message = "Wrong response output data type!";

}