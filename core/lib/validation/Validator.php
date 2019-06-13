<?php
namespace Validation;

use validation\exceptions\WrongMaskException;
use Validation\Field;
use validation\masks\Mask;


class Validator {
    public $validated;
    public $data;
    public $fields;
    public $mask;

    public function __construct($data, $mask = null) {
        $this->data = $data;
        $this->validated = false;
        if ($mask){
            $this->addMask($mask);
        }
    }

    public function addMask($mask) {
        if (is_string($mask)) {
            $className = "App\\Validation\\Masks\\" . $mask;
            $this->mask = new $className();
        } else {
            $this->mask = $mask;
        }
        if ($this->mask instanceof Mask) {
            $this->mask->mask($this);
        } else {
            throw new WrongMaskException();
        }
    }


    public function __get($name) {
        if (isset($this->fields[$name])){
            return $this->fields[$name];
        }
        if (isset($this->data[$name])) {
            $this->fields[$name] = new Field($name, $this->data[$name]);
            return $this->fields[$name];
        } else {
            $this->fields[$name] = new Field($name, "", false);
            return $this->fields[$name];
        }
    }

    public function validate() {
        if (!$this->validated) {
            $this->revalidate();
        }
    }

    public function revalidate() {
        foreach ($this->fields as $field) {
            if ($field->exists) {
                $field->validate();
            }
        }
    }

    public function getAll(){
        $this->validate();
        $result = [];
        foreach ($this->fields as $field){
            if ($field->exists) {
                $result = array_merge($result, $field->get());
            }
        }
        return $result;
    }

    public function get(){
        $this->validate();
        $result = [];
        foreach ($this->fields as $field){
            if ($field->exists){
                if (!$field->error)
                    $result = array_merge($result, $field->get());
            }
        }
        return $result;
    }

    public function errors(){
        $this->validate();
        $errors = [];
        foreach ($this->fields as $field){
            if ($field->error)
                $errors = array_merge($errors, $field->getError());
        }
        return $errors;
    }

}