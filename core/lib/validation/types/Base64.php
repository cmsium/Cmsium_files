<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 3/12/19
 * Time: 5:19 PM
 */

namespace Validation\types;


class Base64 extends ValidationType {
    public $errorMessage = "Value must be valid base64 string";

    public function get() {
        return function ($value) {
            $pattern = "/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/";
            return preg_match($pattern,$value);
        };
    }
}