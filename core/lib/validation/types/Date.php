<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:46 PM
 */

namespace validation\types;


class Date extends ValidationType {
    public $errorMessage = "Value must be valid date";

    public function get() {
        return function ($value,$format) {
            return date_create_from_format($format, $value);
        };
    }
}