<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 1:17 PM
 */

namespace validation\types;

class Ipv4 extends ValidationType {
    public $errorMessage = "Value must be valid ipv4";
    public function get() {
        return function ($value) {
            $pattern = "/\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/";
            return preg_match($pattern,$value);
        };
    }
}