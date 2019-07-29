<?php
namespace Errors\Traits;

trait ValidationException {
    use AppException;
    public $template = "validation_error";
    public $errors;

    public function __construct(array $errors, \Exception $previous = null) {
        parent::__construct("Validation error", 422, $previous);
        $this->errors = $errors;
    }

    public function get() {
        return $this->errors;
    }
}