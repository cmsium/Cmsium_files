<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/22/19
 * Time: 5:46 PM
 */

namespace Validation\exceptions;
use Exception;

class WrongTypeClassException extends Exception {
    protected $message = "Type class must extend 'ValidationType'";
}