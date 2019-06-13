<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/22/19
 * Time: 4:42 PM
 */

namespace Validation\exceptions;
use Exception;

class WrongMaskException extends Exception{
    protected $message = "Mask must implements 'Mask'";
}