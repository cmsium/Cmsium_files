<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 2:23 PM
 */

namespace validation\masks;


class OpenAPIContent extends OpenAPI {

    public function mask ($validator) {
        foreach ($this->structure["properties"] as $name => $field){
            if ($this->getRequired($name)){
                $validator->$name->required();
            }
            if ($this->getNullable($name)){
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

    public function getRequired($name) {
        if (!isset($this->structure['required'])){
            return false;
        }
        return in_array($name, $this->structure['required']);
    }

    public function getNullable($name) {
        if (!isset($this->structure['nullable'])){
            return false;
        }
        return in_array($name, $this->structure['nullable']);
    }

    public function getType ($field) {
        if (isset ($field['format'])){
            $format = $field['format'];
            return $this->alias($format);
        }
        if (!$this->getEnum($field)) {
            $type = $field['type'];
            return $this->alias($type);
        }
    }

    public function getArgs ($field) {
        $args=[];
        if (isset($field['minLength'])){
            $args[] = $field['minLength'];
        }
        if (isset($field['minimum'])){
            $args[] = $field['minimum'];
        }
        if (isset($field['maxLength'])){
            $args[] = $field['maxLength'];
        }
        if (isset($field['maximum'])){
            $args[] = $field['maximum'];
        }
        if (isset($field['args'])){
            $args[] = array_merge($args, $field['args']);
        }
        return $args;
    }

    public function getEnum($field) {
        if (isset($field['enum'])) {
            return $field['enum'];
        } else {
            return false;
        }
    }
}