<?php
namespace Queue\Tasks;

class FilesDeleteTask extends Task {
    public static $structure = [
        'path' => ['type' => 'string', 'size' => 255]
    ];
}