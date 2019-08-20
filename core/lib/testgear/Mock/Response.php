<?php

namespace Testgear\Mock;

use Testgear\HttpAssertable;

class Response {

    use HttpAssertable;

    public $result;
    public $resultFile = [];
    public $isFile;
    public $status;
    public $header;
    public $cookie;

    public function end($content = null) {
        $this->isFile = false;
        $this->result = $content;
    }

    public function sendfile($filename, $offset = null, $length = null) {
        $this->isFile = true;
        $this->resultFile = compact(['filename', 'offset', 'length']);
    }

    public function status($http_code, $reason = null) {
        $this->status = $http_code;
    }

    public function header($key, $value, $ucwords = null) {
        $this->header[$key] = $value;
    }

    public function cookie($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        $this->cookie[] = $this->cookieParamsToString($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    // TODO: rawcookie

    // TODO: Write

    public function gzip($compress_level = null) {
        $this->header('Content-Encoding', 'gzip');
    }

    public function redirect($location, $http_code = null) {
        $this->header('Location', $location);

        if ($http_code) {
            $this->status = $http_code;
        }
    }

    private function cookieParamsToString($name, $value, $expires, $path, $domain, $secure, $httponly) {
        $resultString = "$name=";

        $resultString .= $value ? "$value;" : 'deleted;';
        $resultString .= 'expires=';
        // Thu, 01-Jan-1970 00:16:40 GMT
        $dateFormat = 'D, d-M-Y H:i:s';
        $resultString .= $expires ? gmdate($dateFormat, $expires) : gmdate($dateFormat, time());
        $resultString .= 'GMT;path=';
        $resultString .= $path ? "$path" : '/';

        // Additional params
        if ($domain) {
            $resultString .= ";domain=$domain";
        }
        if ($secure) {
            $resultString .= ';secure';
        }
        if ($httponly) {
            $resultString .= ';httponly';
        }

        return $resultString;
    }

}