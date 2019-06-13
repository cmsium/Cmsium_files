<?php

namespace Files\drivers;

use Files\exceptions\CanNotDeleteFileException;
use Files\exceptions\CouldNotUploadException;
use Files\exceptions\FileNotFoundException;

class Swoole {
    public $thread_num;
    public $aio_mode;
    public $enable_signalfd;
    public $socket_buffer_size;
    public $socket_dontwait;

    public $memory_limit = 4*1024*1024;

    public function __construct($settings = null) {
        if ($settings){
            swoole_async_set($settings);
        }
    }

    public function upload($oldName, $newName) {
        if (!rename($oldName, $newName)){
            throw new CouldNotUploadException();
        }
    }

    public function write($file, $content) {
        if (!is_dir(dirname($file->path))) {
            mkdir(dirname($file->path), 0775, true);
        }
        if ($file->size <= $this->memory_limit){
            swoole_async_writefile($file->path, $content);
        } else {
            swoole_async_write($file->path, $content, 0);
        }
        return true;
    }

    public function read($file,$callback) {
        $this->exists($file);
        if ($file->size <= $this->memory_limit){
            swoole_async_readfile($file->path,$callback);
        } else {
            swoole_async_read($file->path,$callback);
        }
    }

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

    public function send($file,$response) {
        $response->header('Content-Description', 'File Transfer');
        $response->header('Content-Type', 'application/octet-stream');
        $response->header('Content-Disposition', 'attachment; filename="'.$file->name.'"');
        $response->header('Expires', '0');
        $response->header('Cache-Control', 'must-revalidate');
        $response->header('Pragma', 'public');
        $response->header('Content-Length', "$file->size");
        return $response->sendfile($file->path);
    }

    public function sendChunked($file, $response, $chunkSize) {
        $filesize = $file->size;
        $from = 0;
        $to = $filesize;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=')+1);
            $from = (integer)(strtok($range, "-"));
            $to = (integer)(strtok("-"));
            $response->status(206);
            $response->header('Content-Range', 'bytes '.$from.'-'.($to-1).'/'.$filesize);
        } else {
            $response->status(200);
        }
        $response->header('Accept-Ranges', 'bytes');
        $response->header('Content-Length', ($filesize-$from));
        header('Content-Type', 'application/octet-stream');
        header('Content-Disposition', 'attachment; filename="' . $file->name . '";');
        $size = $to - $from;
        $offset = 0;
        while($offset < $size) {
            $response->sendfile($file->path, $offset, $chunkSize);
            $offset += $chunkSize;
        }
        fclose($file);
    }
}