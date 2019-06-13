<?php
namespace Queue\Tasks;

class Task {
    public static $structure = [];
    public static $supportedTypes = ['string', 'int'];

    public $id;
    public $queryTag;
    public $ttl = null;
    public $status = 0;
    public $data = [];

    public function __construct($queryTag, $data, $ttl = null) {
        $this->queryTag = $queryTag;
        $this->data = $data;
        $this->ttl = $ttl;
    }

    public static function row($name, $type, $size) {
        self::$structure[$name] = ['type' => $type, 'size' => $size];
    }
}