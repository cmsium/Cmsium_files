<?php
namespace App\Validation\Masks;

class SaveLink extends \Validation\masks\OpenAPIContent {
public $structure = 
[
    'type' => 'object',
    'properties' => [
        'hash' => [
            'type' => 'string',
            'format' => 'base64',
        ],
        'file' => [
            'type' => 'string',
            'format' => 'md5',
        ],
        'temp' => [
            'type' => 'boolean',
        ],
        'expire' => [
            'type' => 'string',
            'format' => 'date-time',
        ],
        'type' => [
            'type' => 'string',
            'enum' => [
                0 => 'read',
                1 => 'upload',
                2 => 'delete'
            ],
        ],
    ],
    'required' => [
        0 => 'hash',
        1 => 'file',
        2 => 'temp',
        3 => 'type',
    ],
    'example' => [
        'hash' => 'MzhjMjUwNzc5ZjRhNzcxNTg3MzRmZDNjZGZjMzBiZjU=',
        'file' => '38c250779f4a77158734fd3cdfc30bf5',
        'temp' => 'true',
        'expire' => '2017-07-21T17:32:28Z',
        'type' => 'read',
    ],
]
;
}