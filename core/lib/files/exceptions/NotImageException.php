<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/5/19
 * Time: 4:11 PM
 */

namespace Files\exceptions;


class NotImageException extends \Exception {
    protected $message = "File not a real image";
}