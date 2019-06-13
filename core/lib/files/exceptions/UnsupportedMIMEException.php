<?php
namespace Files\exceptions;
use Exception;

class UnsupportedMIMEException extends Exception {
    protected $message = "Unsupported mime type";
}