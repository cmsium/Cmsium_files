<?php

function checkFile($file_id,$path){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        echo json_encode(["status" => "error", "message" => "Wrong file id"]);
        return;
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong file path format"]);
        return;
    }
    if (!checkIntegrity($file_id,$path)) {
        echo json_encode(["status" => "error", "message" => "File was corrupted or removed"]);
        return;
    }
    $link_existence = getLink($path);
    if (!$link_existence){
        echo json_encode(["status" => "nolink"]);
        return;
    } else {
        echo json_encode(["status" => "link", "link" => $link_existence['link']]);
        return;
    }
}

function getFile($link){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if ($link === false){
        echo json_encode(["status" => "error", "message" => "Wrong link format"]);
        return;
    }
    $link_existence = getFileByLink($link);
    if (!$link_existence){
        echo json_encode(["status" => "error","message" => "File not found"]);
        return;
    }
    readFileWithSpeed($link_existence['file_path'],"asdads");
}

function saveTempLink($path,$link){
    var_dump($path,$link);
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if ($link === false){
        echo json_encode(["status" => "error", "message" => "Wrong link format"]);
        return;
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong file path format"]);
        return;
    }
    if (!saveLink($path,$link)){
        echo json_encode(["status" => "error", "message" => "Link save error"]);
        return;
    }
    echo json_encode(["status" => "ok"]);
    return;
}



