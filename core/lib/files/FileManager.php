<?php
namespace Files;
use Exception;
use File;
use Files\exceptions\UnsupportedMIMEException;


class FileManager {
    public $files = [];
    public $uploadFiles = [];

    public $driver;
    public $types = [
        'image/jpg' => Image::class,
        'image/jpeg' => Image::class,
        'image/png' => Image::class,
        'application/pdf' => PDF::class,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'text/plain'
    ];

    public function __construct($driver) {
        $this->driver = $driver;
    }

    public function upload($filesData, $newPath) {
        $this->registerUploadFiles($filesData);
        $this->registerFiles($this->uploadFiles);
        foreach ($this->files as $key => $file){
            $this->driver->upload($this->uploadFiles[$key]->tmp_name, $newPath);
            $file->path = $newPath;
        }
        return $this->get();
    }

    public function get($key = null) {
        if ($key) {
            return $this->files[$key] ?? null;
        } else {
            return $this->files;
        }
    }

    public function getUpload($key = null) {
        if ($key) {
            return $this->uploadFiles[$key] ?? null;
        } else {
            return $this->uploadFiles;
        }
    }

    public function create($path) {
        if (is_string($path)){
            $file = (new File($path))->with(['driver' => $this->driver]);
            $this->files[] = $file;
            return $file;
        } elseif ($path instanceof File){
            $path->with(['driver' => $this->driver]);
            $this->files[] = $path;
            return $path;
        } else {
            // TODO: Set exception explicitly
            throw new Exception("File must extend 'File' class");
        }
    }

    public function registerFiles($files) {
        foreach ($files as $key => $value){
            $class = $this->defineType($value->type);
            $this->files[$key] = (new $class())->with(array_merge($value->getMetaData(),['driver' => $this->driver]));
        }
    }

    public function registerUploadFiles($filesData) {
        foreach ($filesData as $key => $value) {
            $this->uploadFiles[$key] = new UploadedFile($value);
        }
    }

    public function defineType($type) {
        if (!key_exists($type, $this->types)){
            throw new UnsupportedMIMEException();
        }
        return $this->types[$type];
    }
}