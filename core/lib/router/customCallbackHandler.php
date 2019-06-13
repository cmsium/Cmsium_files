<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 3/29/19
 * Time: 2:24 PM
 */

namespace Router;


class customCallbackHandler implements CallbackHandler {

    public function before() {
        return function ($string) {
            var_dump("before: $string");
        };
    }

    public function after() {
        return function () {
            return false;
        };
    }
}