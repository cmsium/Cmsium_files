<?php
namespace Files;
use File;
use Files\exceptions\NotImageException;

class Image extends File {
    public $thumbFormat = 'png';
    public $thumbSize = 100;
    public $thumbPath;
    public $thumbName;

    public function makeThumbnail($thumbPath = null) {
        if ($thumbPath){
            $this->thumbPath = $thumbPath;
        }
        $image = new Imagick($this->path);
        $image->setImageFormat($this->thumbFormat);
        $image->thumbnailImage($this->thumbSize, 0);
        $name = $this->generateThumbName();
        $result = $image->writeImage($this->thumbPath."/".$name);
        return $result;
    }

    public function generateThumbName(){
        $name = "$this->name.$this->thumbFormat";
        $this->thumbName = $name;
        return $name;
    }

    public function check() {
        $check = getimagesize($this->path);
        if($check === false) {
            throw new NotImageException();
        }
    }
}