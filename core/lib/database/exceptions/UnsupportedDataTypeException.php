<?php

namespace DB\Exceptions;

use Exception;

class UnsupportedDataTypeException extends Exception {

    protected $message = "Unsupported data type given.";

}