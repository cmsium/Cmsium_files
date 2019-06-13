<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:34 PM
 */

namespace Validation\types;


class Url extends ValidationType {
    public $errorMessage = "Value must be valid url";
    public function get() {
        return function ($value) {
            return filter_var($value, FILTER_VALIDATE_URL);
        };
    }
}