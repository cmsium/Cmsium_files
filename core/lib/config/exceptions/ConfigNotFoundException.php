<?php

namespace Config\Exceptions;

use Exception;

class ConfigNotFoundException extends Exception {

    protected $message = "Can't find configuration file.";

}