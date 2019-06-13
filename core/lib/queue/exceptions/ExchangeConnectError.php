<?php
namespace Queue\Exceptions;

class ExchangeConnectError extends \Exception {
    protected $message = "Exchange connect error";
    protected $code = 500;
}