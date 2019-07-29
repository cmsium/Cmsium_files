<?php

namespace DB\Exceptions;

use Exception;

class MigrationException extends Exception {

    protected $message = "Could not migrate DB.";

}