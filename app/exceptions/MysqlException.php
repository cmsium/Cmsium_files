<?php


namespace App\Exceptions;


class MysqlException extends \Exception {
    protected $message = "mysql error";
    protected $code = 500;
}