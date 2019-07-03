<?php
namespace Queue\Tasks;

class DeleteTask extends Task {
    public static $structure = [
        'path' => ['type' => 'string', 'size' => 255],
    ];
}