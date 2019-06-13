<?php
namespace Files\drivers;
use Files\exceptions\CanNotDeleteFileException;
use Files\exceptions\CanNotReadFileException;
use Files\exceptions\CanNotWriteFileException;
use Files\exceptions\CouldNotUploadException;
use Files\exceptions\FileNotFoundException;

class Native {

    public function upload($oldName, $newName) {
        if (!move_uploaded_file($oldName, $newName)){
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

    public function exists($file) {
        if (!file_exists($file->path))
            throw new FileNotFoundException();
        return true;
    }

    public function delete($file) {
        $this->exists($file->path);
        if (!unlink($file->path)) {
            throw new CanNotDeleteFileException();
        }
        return true;
    }

    public function send($file) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file->name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file->size);
        ob_clean();
        readfile($file->path);
    }

    public function sendChunked($file,$chunkSize) {
        $filesize = $file->size;
        $from = 0;
        $to = $filesize;
        ob_start();
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=')+1);
            $from = (integer)(strtok($range, "-"));
            $to = (integer)(strtok("-"));
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes '.$from.'-'.($to-1).'/'.$filesize);
        } else {
            header('HTTP/1.1 200 Ok');
        }
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($filesize-$from));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file->name . '";');
        $file = fopen($file->path, 'rb');
        fseek($file, $from);
        $size = $to - $from;
        $downloaded = 0;
        while(!feof($file) and ($downloaded<$size)) {
            echo fread($file, $chunkSize);
            flush();
            ob_flush();
            $downloaded += $chunkSize;
        }
        fclose($file);
    }
}