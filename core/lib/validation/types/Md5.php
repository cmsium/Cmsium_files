<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:16 PM
 */

namespace Validation\types;

class Md5 extends ValidationType {
    public $errorMessage = "Value must be valid md5 hash";
    public function get() {
        return function ($value) {
            $pattern = "/^[\da-f]{32}$/i";
            return preg_match($pattern,$value);
        };
    }
}