<?php
namespace App\Exceptions;


class ValidationException extends \Exception {
    use  \Errors\Traits\ValidationException;
}