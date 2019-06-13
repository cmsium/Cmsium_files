<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 3/29/19
 * Time: 2:26 PM
 */

namespace Router;


interface CallbackHandler {

    public function before();

    public function after();

}