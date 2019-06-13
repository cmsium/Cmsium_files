<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/5/19
 * Time: 4:07 PM
 */

namespace Files\exceptions;


class FileNotFoundException extends \Exception {
    protected $message = "File not found";
}