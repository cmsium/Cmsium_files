<?php
function sendRequest($URL,$method,$header,$content){
    $options = ['http' => ['method' => $method, 'header' => $header, 'content' => $content]];
    $context = stream_context_create($options);
    return file_get_contents("http://$URL", false, $context);
}