<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/5/19
 * Time: 4:08 PM
 */

namespace Files\exceptions;


class CanNotDeleteFileException extends \Exception {
    protected $message = "Can not delete file";
}