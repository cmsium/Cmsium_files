<?php

namespace DB\Exceptions;

use Exception;

class DBConnectionException extends Exception {

    protected $message = "Can't connect to the database!";

}