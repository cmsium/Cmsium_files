<?php
namespace Validation\types;

class Integer extends ValidationType {

    public $errorMessage = "Value must be integer";
    public function get() {
        return function ($value, $max = 20) {
            if (is_integer($value)){
                return true;
            } elseif (is_string($value)){
                $pattern = "/^\d{1,{$max}}$/";
                $result =  preg_match($pattern,$value);
                return $result;
            }
        };
    }
}