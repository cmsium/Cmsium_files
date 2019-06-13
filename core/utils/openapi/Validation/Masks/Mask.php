<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 1/22/19
 * Time: 4:32 PM
 */

namespace validation\masks;

interface Mask {

    public function mask($validator);
}