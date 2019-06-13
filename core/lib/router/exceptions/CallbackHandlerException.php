<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 3/29/19
 * Time: 2:41 PM
 */

namespace Router\exceptions;


class CallbackHandlerException extends \Exception {
    protected $message = "callback handler class should implement 'callbackHandler'";
}