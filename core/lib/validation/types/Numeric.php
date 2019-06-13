<?php
namespace Validation\types;

class Numeric extends ValidationType {
    public $errorMessage = "Value must be numeric";

    public function get() {
        return function ($value,$min,$max) {
            $pattern = "/^\d{{$min},{$max}}$/";
            $result =  preg_match($pattern,$value);
            return $result;
        };
    }
}