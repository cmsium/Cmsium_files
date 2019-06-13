<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/23/19
 * Time: 12:05 PM
 */

namespace Validation\types;


class Regexp extends ValidationType {
    public $errorMessage = "Wrong value";

    public function get() {
        return function ($value, $pattern) {
            if (is_array($pattern)) {
                foreach ($pattern as $regexp){
                    if (!preg_match("/^$regexp$/",$value)){
                        return false;
                    }
                }
                return true;
            } else {
                return preg_match("/^$pattern$/", $value);
            }
        };
    }
}