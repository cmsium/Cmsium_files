<?php

namespace DB\Exceptions;

use Exception;

class TransactionException extends Exception {

    protected $message = "Could not establish correct db transaction.";

}