<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:06 PM
 */

namespace Validation\types;

class RangedInt extends ValidationType {
    public $errorMessage = "Value must be integer";

    public function get() {
        return function ($value,$min,$max) {
            return (is_integer($value) and $value >= $min and $value <= $max);
        };
    }
}