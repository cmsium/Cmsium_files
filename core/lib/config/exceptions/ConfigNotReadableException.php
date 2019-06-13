<?php

namespace Config\Exceptions;

use Exception;

class ConfigNotReadableException extends Exception {

    protected $message = "Can't read configuration.";

}