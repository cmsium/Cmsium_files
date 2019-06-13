<?php
namespace App\Validation\Masks;

class DeleteFile extends \Validation\masks\OpenAPIParameters {
public $structure = 
[
    0 => [
        'name' => 'hash',
        'in' => 'path',
        'description' => 'A hash of the file to delete',
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