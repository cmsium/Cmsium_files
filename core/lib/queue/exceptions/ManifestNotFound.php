<?php
namespace Queue\Exceptions;

class ManifestNotFound extends \Exception {
    protected $message = "Queues manifest not found";
    protected $code = 500;
}