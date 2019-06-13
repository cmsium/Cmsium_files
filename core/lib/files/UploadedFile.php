<?php
namespace Files;

class UploadedFile {
    public $name;
    public $tmp_name;
    public $type;
    public $size;

    public function __construct($data) {
        $this->name = $data['name'];
        $this->tmp_name = $data['tmp_name'];
        $this->type = $data['type'];
        $this->size = (int)$data['size'];
    }

    public function getMetaData() {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
        ];
    }
}