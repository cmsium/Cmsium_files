<?php

namespace Validation\masks;

class DefaultMask implements Mask {
    public $structure;
    public $aliaces;

    public function __construct ($structure = null) {
        if ($structure){
            $this->structure = $structure;
        }
    }

    public function mask ($validator) {
        foreach ($this->structure as $name => $field){
            if ($this->getRequired($field)){
                $validator->$name->required();
            }
            if ($this->getNullable($field)){
                $validator->$name->nullable();
            }
            if ($type = $this->getType($field)) {
                $args = $this->getArgs($field);
                $validator->$name->$type(...$args);
            }
        }
    }

    public function alias($key) {
        if (key_exists($key, $this->aliaces)){
            return $this->aliaces[$key];
        } else {
            return $key;
        }
    }

    public function getRequired ($field) {
        return $field['required'] ?? false;
    }

    public function getNullable ($field) {
        return $field['nullable'] ?? false;
    }

    public function getType ($field) {
        return $field['type'] ?? false;
    }

    public function getArgs ($field) {
        return $field['args'] ?? [];
    }
}