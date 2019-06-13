<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/8/19
 * Time: 5:31 PM
 */

namespace Validation\types;


class Float extends ValidationType {
    public $errorMessage = "Value must be float";

    public function get() {
        return function ($value, $max = 20) {
            if (is_float($value)){
                return true;
            } elseif (is_string($value)){
                $pattern = "/^(\d+.\d+){1,{$max}}$/";
                $result =  preg_match($pattern,$value);
                return $result;
            }
        };
    }
}