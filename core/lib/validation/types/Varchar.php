<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:11 PM
 */

namespace Validation\types;


class Varchar extends ValidationType {
    public $errorMessage = "Value must be latin or cirrilic alphabetic, numeric or contain special symbols like ',' '.' '-', '/', '_'";

    public function get() {
        return function ($value,$max) {
            $pattern = "@^[а-яА-ЯёЁa-zA-Z\d\-\s\,\.\\\/\_]{1,{$max}}$@u";
            return preg_match($pattern,$value);
        };
    }
}