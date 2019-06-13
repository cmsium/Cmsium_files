<?php
namespace Validation;

use Validation\exceptions\WrongTypeClassException;
use validation\types\ValidationType;

class Field {
    public $key;
    public $value;

    public $callbackClasses;
    public $error;
    public $required;
    public $requiredMessage = "Value must exist in input";
    public $exists;
    public $nullable;
    public $nullableMessage = "Value should not be empty";

    public function __construct($key, $value, $exists = true) {
        $this->key = $key;
        $this->value = $value;
        $this->error = false;
        $this->required = false;
        $this->exists = $exists;
        $this->nullable = false;
    }

    public function sanitize (){
        $value = trim($this->value);
        $value = strip_tags($value);
        $value = stripcslashes($value);
        $this->value = htmlspecialchars($value);
        return $this;
    }

    public function required() {
        $this->required = true;
        if (!$this->exists){
            $this->error = $this->requiredMessage;
        }
        return $this;
    }

    public function nullable() {
        $this->nullable = true;
        return $this;
    }

    public function checkNullable() {
        if ($this->nullable){
            if (empty($this->value)){
                return false;
            } else {
                return true;
            }
        } else {
            if (empty($this->value) and $this->value !== 0 and $this->value !== "0" and $this->value !== 0.0){
                $this->error = $this->nullableMessage;
                return false;
            } else {
                return true;
            }
        }
    }


    public function get() {
        return [$this->key => $this->value];
    }

    public function getError() {
        return [$this->key => $this->error];
    }

    public function __call($method, $args) {
        $className = "Validation\\types\\" . ucfirst($method);
        $callbackClass = new $className();
        if ($callbackClass instanceof ValidationType) {
            $callbackClass->args = $args;
            $this->callbackClasses[$method] = $callbackClass;
            return $this;
        } else {
            throw new WrongTypeClassException();
        }
    }

    public function validate() {
        if ($this->exists){
            if ($this->checkNullable()) {
                foreach ($this->callbackClasses as $callbackClass) {
                    if (!$this->error) {
                        $callback = $callbackClass->get();
                        $args = $callbackClass->args;
                        $value = $callback($this->value, ...$args);
                        if (!$value) {
                            if ($err = $callbackClass->errorMessage)
                                $this->error = $err;
                            else
                                $this->error = true;
                        }
                    }
                }
            }
        }
    }

}