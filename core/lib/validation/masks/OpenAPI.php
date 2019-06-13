<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2/14/19
 * Time: 2:33 PM
 */

namespace validation\masks;


class OpenAPI extends DefaultMask {
    // TODO: change aliaces to aliases
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
}