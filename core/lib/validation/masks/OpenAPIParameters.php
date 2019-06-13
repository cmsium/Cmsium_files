<?php

namespace Validation\masks;

class OpenAPIParameters extends OpenAPI {
    public $aliaces = [
        "integer" => "integer",
        "int32" => "integer",
        "int64" => "integer",
        "number" => "float",
        "float" => "float",
        "double" => "float",
        "byte" => "base64",
        "binary" => "binary",
        "date" => "date3339",
        "date-time" => "date3339",
    ];

    public function mask ($validator) {
        foreach ($this->structure as $field){
            $name = $field["name"];
            if ($this->getRequired($field)){
                $validator->$name->required();
            }
            if ($this->getNullable($field)){
                $validator->$name->nullable();
            }
            if ($list = $this->getEnum($field)){
                $validator->$name->ValueFromList($list);
            }
            if ($type = $this->getType($field)) {
                $args = $this->getArgs($field);
                $validator->$name->$type(...$args);
            }
        }
    }

    public function getType ($field) {
        if (isset($field['schema'])) {
            if (isset ($field['schema']['format'])){
                $format = $field['schema']['format'];
                return $this->alias($format);
            }
            if (!$this->getEnum($field)) {
                $type = $field['schema']['type'];
                return $this->alias($type);
            }
        } else {
            return false;
        }
    }

    public function getArgs ($field) {
        $args=[];
        if (isset($field['schema'])) {
            if (isset($field['schema']['minLength'])){
                $args[] = $field['schema']['minLength'];
            }
            if (isset($field['schema']['minimum'])){
                $args[] = $field['schema']['minimum'];
            }
            if (isset($field['schema']['maxLength'])){
                $args[] = $field['schema']['maxLength'];
            }
            if (isset($field['schema']['maximum'])){
                $args[] = $field['schema']['maximum'];
            }
            if (isset($field['schema']['args'])){
                $args[] = array_merge($args, $field['schema']['args']);
            }
        }
        return $args;
    }

    public function getEnum($field) {
        if (isset($field['schema']['enum'])) {
            return $field['schema']['enum'];
        } else {
            return false;
        }
    }
}