<?php

namespace Validation\types;


class Boolean extends ValidationType {
    public $errorMessage = "Value must valid boolean type (true/false 'true'/'false' 'yes'/'no' ...)";

    public function get() {
        return function ($value,$customList=null) {
            $list = [true,false,"true","false","yes","no",1,0,"1","0","ok"];
            if ($customList) {
                $list = array_merge($list,$customList);
            }
            return in_array($value,$list,true);
        };
    }
}