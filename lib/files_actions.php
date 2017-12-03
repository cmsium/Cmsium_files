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


function getFile($link,$name){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if ($link === false){
        echo json_encode(["status" => "error", "message" => "Wrong link format"]);
        return;
    }
    $name = $validator->Check('fileName',$name,['min'=>1,'max'=>255,'types'=>FILES_ALLOWED_TYPES]);
    if ($name === false){
        echo json_encode(["status" => "error", "message" => "Wrong file name"]);
        return;
    }
    $link_existence = getFileByLink($link);
    if (!$link_existence){
        echo json_encode(["status" => "error","message" => "File not found"]);
        return;
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkUserConnects($ip)){
        echo json_encode(["status" => "error","message" => "Too much connections for this user"]);
        return;
    }
    if (!registerConnect($ip,$link_existence['file_path'])){
        echo json_encode(["status" => "error","message" => "You are already downloading this file"]);
        return;
    }
    $speed = resolveDownloadSpeed();
    readFileWithSpeed('/'.$link_existence['file_path'],$name,$speed);
    deleteConnect($ip,$link_existence['file_path']);
}


function getFileByPath($path){
    $validator = Validator::getInstance();
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong link format"]);
        return;
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkUserConnects($ip)){
        echo json_encode(["status" => "error","message" => "Too much connections for this user"]);
        return;
    }
    if (!registerConnect($ip,$path)){
        echo json_encode(["status" => "error","message" => "You are already downloading this file"]);
        return;
    }
    $speed = resolveDownloadSpeed();
    readFileWithSpeed('/'.$path,null,$speed);
    deleteConnect($ip,$path);
}


function saveTempLink($path,$link){
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
        if (!checkMime($_FILES['userfile']['tmp_name'],end(explode('.',$_FILES['userfile']['name'])))) {
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
        $file_name = $validator->Check('Md5Type',$file_name,['types'=>FILES_ALLOWED_TYPES]);
        if ($file_name === false){
            echo json_encode(["status" => "error", "message" => "Wrong file name"]);
            return;
        }
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            echo json_encode(["status" => "error", "message" => "Wrong file format"]);
            return;
        }
        if (!$type = checkMime($_FILES['userfile']['tmp_name'],end(explode('.',$_FILES['userfile']['name'])))) {
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
            //makeThumbnail($file_name,$type);
            $url = Config::get('host_url');
            echo json_encode(["status" => "ok", "path" => $url."/$fullpath"]);
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
    if (!unlink('/'.$path)){
        echo json_encode(["status" => "error", "message" => "Delete file error"]);
        return;
    }
    echo json_encode(["status" => "ok"]);
    return;
}



function moveFile($server,$path){
    $validator = Validator::getInstance();
    $server = $validator->Check('Path',$server,[]);
    if ($server === false){
        echo json_encode(["status" => "error", "message" => "Wrong server format"]);
        exit;
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        echo json_encode(["status" => "error", "message" => "Wrong file path format"]);
        return;
    }
    $dest_server = @explode('//',$path)[0];
    $self = Config::get('host_url');
    if ($dest_server == $self){
        echo json_encode(["status" => "error", "message" => 'File already on this server']);
        exit;
    }
    $name = @end(explode('/',$path));
    $response = SendFile($server."/createFile?file_name=$name",'/'.$path,$name);
    switch ($response['status']){
        case 'error':
            echo json_encode(["status" => "error", "message" => $response['message']]);
            exit;
        case 'ok':
            $file_path = $response['path'];
            break;
    }
    unlink('/'.$path);
    echo  json_encode(["status" => "ok", "file_path" => $file_path]);
    return;
}

function serverStatus(){
    $files = scandir(ROOTDIR.'/'.STORAGE,SCANDIR_SORT_NONE);
    $size = 0;
    if (!empty($files)){
    foreach ($files as $file) {
            if ($file != '.' and $file != '..') {
                $size += filesize(ROOTDIR.'/'.STORAGE.'/'.$file);
            }
        }
    }
    echo json_encode(["status" => "ok","free_disk_space"=>$size]);
}


function getAllFiles(){
    $files = scandir(ROOTDIR.'/'.STORAGE);
    $result=[];
    foreach ($files as $file){
        if ($file != '.' and $file != '..'){
            $result[]=realpath(ROOTDIR.'/'.STORAGE.'/'.$file);
        }
    }
    echo json_encode(array_merge(["status" => "ok"],$result));
}


