<?php
namespace Files;


class PDF extends Image {

    public function __construct($path = null) {
        parent::__construct($path);
        $this->path = $path."[0]";
    }
}