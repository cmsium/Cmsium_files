<?php
namespace App\Validation\Masks;

class UploadFile extends \Validation\masks\OpenAPIParameters {
public $structure = 
[
    0 => [
        'name' => 'hash',
        'in' => 'path',
        'description' => 'An upload hash received from controller',
        'required' => true,
        'schema' => [
            'type' => 'string',
            'format' => 'base64',
        ],
        'style' => 'simple',
    ],
]
;
}