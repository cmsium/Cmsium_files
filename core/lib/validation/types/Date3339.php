<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/8/19
 * Time: 5:29 PM
 */

namespace Validation\types;


class Date3339 extends ValidationType {
    public $errorMessage = "Value must be valid date (RFC 3339)";

    public function get() {
        return function ($value) {
            $pattern = "/^(\d+)-(0[1-9]|1[012])-(0[1-9]|[12]\d|3[01])T([01]\d|2[0-3]):([0-5]\d):([0-5]\d|60)(\.\d+)?(([Zz])|([\+|\-]([01]\d|2[0-3]):[0-5][0-9]))$/";
            return preg_match($pattern,$value);
        };
    }
}