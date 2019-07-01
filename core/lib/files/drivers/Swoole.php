<?php

namespace Files\drivers;

use Files\exceptions\CanNotDeleteFileException;
use Files\exceptions\CanNotReadFileException;
use Files\exceptions\CanNotWriteFileException;
use Files\exceptions\CouldNotUploadException;
use Files\exceptions\FileNotFoundException;

class Swoole {
    public $thread_num;
    public $aio_mode;
    public $enable_signalfd;
    public $socket_buffer_size;
    public $socket_dontwait;

    public function __construct($settings = null) {
        if ($settings){
            swoole_async_set($settings);
        }
    }

    public function upload($oldName, $newName) {
        if (!is_dir(dirname($newName))) {
            mkdir(dirname($newName), 0777, true);
        }
        if (!rename($oldName, $newName)){
            throw new CouldNotUploadException();
        }
    }

    public function write($file, $content) {
        if (!is_dir(dirname($file->path))) {
            mkdir(dirname($file->path), 0775, true);
        }
        if (!file_put_contents($file->path, $content)) {
            throw new CanNotWriteFileException();
        }
        return true;
    }

    public function read($file) {
        $this->exists($file->path);
        if (file_get_contents($file->path) === false) {
            throw new CanNotReadFileException();
        }
    }


//    //deprecated
//    public function write($file, $content) {
//        if (!is_dir(dirname($file->path))) {
//            mkdir(dirname($file->path), 0775, true);
//        }
//        if ($file->size <= $this->memory_limit){
//            swoole_async_writefile($file->path, $content);
//        } else {
//            swoole_async_write($file->path, $content, 0);
//        }
//        return true;
//    }
//
//    //deprecated
//    public function read($file,$callback) {
//        $this->exists($file);
//        if ($file->size <= $this->memory_limit){
//            swoole_async_readfile($file->path,$callback);
//        } else {
//            swoole_async_read($file->path,$callback, $this->memory_limit);
//        }
//    }

    public function exists($file) {
        if (!file_exists($file->path))
            throw new FileNotFoundException();
        return true;
    }

    public function delete($file) {
        $this->exists($file);
        if (!unlink($file->path)) {
            throw new CanNotDeleteFileException();
        }
        return true;
    }

    public function send($file,$app) {
        $app->setHeader('Content-Description', 'File Transfer');
        $app->setHeader('Content-Type', 'application/octet-stream');
        $app->setHeader('Content-Disposition', 'attachment; filename="'.$file->name.'"');
        $app->setHeader('Expires', '0');
        $app->setHeader('Cache-Control', 'must-revalidate');
        $app->setHeader('Pragma', 'public');
        $app->setHeader('Content-Length', "$file->size");
        //return $response->sendfile($file->path);
        return $app->respondFile($file->path);
    }

    public function sendChunked($file, $app,  $chunkSize) {
        $filesize = $file->size;
        $from = 0;
        $to = $filesize;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=')+1);
            $from = (integer)(strtok($range, "-"));
            $to = (integer)(strtok("-"));
            $app->setStatusCode(206);
            $app->setHeader('Content-Range', 'bytes '.$from.'-'.($to-1).'/'.$filesize);
        } else {
            $app->setStatusCode(200);
        }
        $app->setHeader('Accept-Ranges', 'bytes');
        $app->setHeader('Content-Length', ($filesize-$from));
        $app->setHeader('Content-Type', 'application/octet-stream');
        $app->setHeader('Content-Disposition', 'attachment; filename="' . $file->name . '";');
        $size = $to - $from;
        $offset = 0;
        $fh = fopen($file->path, "r");
        if ($fh === false){
            throw new CanNotReadFileException();
        }
        while($offset < $size) {
            $data = fread($fh, $chunkSize);
            $app->response->write($data);
            $offset += $chunkSize;
        }
        fclose($fh);
        return true;
    }
}