<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:35 PM
 */

namespace validation\types;


class Email extends ValidationType {
    public $errorMessage = "Value must be valid email";

    public function get() {
        return function ($value) {
            $pattern = "/^([\w\dа-яА-Я-\.?]{1,})\@([\w\d-\.?]{1,})$/u";;
            return preg_match($pattern,$value);
        };
    }
}