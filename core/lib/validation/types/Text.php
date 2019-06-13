<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:13 PM
 */

namespace Validation\types;


class Text extends ValidationType {
    public $errorMessage = "Value should not contain forbidden symbols";
    public function get() {
        return function ($value,$except,$min,$max) {
            $pattern = "@^([^{$except}]){{$min},{$max}}$@u";
            return preg_match($pattern,$value);
        };
    }
}