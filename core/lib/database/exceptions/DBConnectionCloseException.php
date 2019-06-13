<?php

namespace DB\Exceptions;

use Exception;

class DBConnectionCloseException extends Exception {

    protected $message = "Can't close database connection!";

}