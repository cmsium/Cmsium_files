<?php
namespace Validation\masks;

class MaskExample implements Mask {

    public function mask($validator) {
        $validator->logintype->valueFromList(['phone','email','username'])->required();
        $validator->login->latinName(1,32)->required();
        $validator->password->alphaNumeric(1,32)->required();
    }
}