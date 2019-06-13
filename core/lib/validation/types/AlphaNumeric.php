<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/22/19
 * Time: 4:12 PM
 */

namespace validation\types;


class AlphaNumeric extends ValidationType {

    public $errorMessage = "Value must be numeric or alphabetic";

    public function get() {
        return function ($value,$min,$max) {
            $pattern = "/^[\w\d-_]{{$min},{$max}}$/";
            return preg_match($pattern,$value);
        };
    }
}