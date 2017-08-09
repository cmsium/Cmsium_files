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
    if (!checkIntegrity($file_id,'/'.$path)) {
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

function generateFileId(){
    if (!empty($_FILES)) {
        $validator = Validator::getInstance();
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            echo json_encode(["status" => "error", "message" => "Wrong file format"]);
            return;
        }
        if (!checkMime($_FILES['userfile']['tmp_name'])) {
            echo json_encode(["status" => "error", "message" => "Wrong file type"]);
            return;
        }
        $size = filesize($_FILES['userfile']['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            echo json_encode(["status" => "error", "message" => "File is too large"]);
            return;
        }
        echo json_encode(["status"=>'ok',"id"=>md5_file($_FILES['userfile']['tmp_name'])]);
        return;
    }
}

function createFile($file_name){
    if (!empty($_FILES)) {
        $validator = Validator::getInstance();
        $file_name = $validator->Check('Md5Type',$file_name,[]);
        if ($file_name === false){
            echo json_encode(["status" => "error", "message" => "Wrong file name"]);
            return;
        }
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            echo json_encode(["status" => "error", "message" => "Wrong file format"]);
            return;
        }
        if (!checkMime($_FILES['userfile']['tmp_name'])) {
            echo json_encode(["status" => "error", "message" => "Wrong file type"]);
            return;
        }
        $size = filesize($_FILES['userfile']['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            echo json_encode(["status" => "error", "message" => "File is too large"]);
            return;
        }
        $path = detectUploadPath();
        $fullpath = "$path/$file_name";
        if (upload($_FILES['userfile']['tmp_name'],$fullpath)){
            //addToZip();
            echo json_encode(["status" => "ok", "path" => HOST_URL."/$fullpath"]);
            return;
        } else {
            echo json_encode(["status" => "error", "message" => "Create file error"]);
            return;
        }
    }
}

function deleteFile($path){
    $validator = Validator::getInstance();
    $file_name = $validator->Check('Path',$path,[]);
    if ($file_name === false){
        echo json_encode(["status" => "error", "message" => "Wrong path format"]);
        return;
    }
    unlink($path);
}



