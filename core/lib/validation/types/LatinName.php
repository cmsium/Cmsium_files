<?php

namespace Validation\types;


class LatinName extends ValidationType {

    public $errorMessage = "Value must be alphabetic";
    public function get() {
        return function ($value,$min,$max) {
            $pattern = "/^[a-zA-z_]{{$min},{$max}}$/";
            return preg_match($pattern,$value);
        };
    }

}