<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/21/19
 * Time: 5:30 PM
 */

namespace Validation\types;


class ValueFromList extends ValidationType {
    public $errorMessage = "Value not found";

    public function get() {
        return function ($value,$list) {
            return in_array($value,$list);
        };
    }
}