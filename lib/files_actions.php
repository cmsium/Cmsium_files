<?php

function checkFile($file_id,$path){
    $validator = Validator::getInstance();
    $file_id = $validator->Check('Md5Type',$file_id,[]);
    if ($file_id === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if (!checkIntegrity($file_id,'/'.$path)) {
        throwException(CORRUPTED_FILE);
    }
    $link_existence = getLink($path);
    if ($link_existence){
        echo $link_existence['link'];
        return;
    }
}


function getFile($link,$name){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if ($link === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $name = $validator->Check('fileName',$name,['min'=>1,'max'=>255,'types'=>FILES_ALLOWED_TYPES]);
    if ($name === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $link_existence = getFileByLink($link);
    if (!$link_existence){
        throwException(FILE_NOT_FOUND);
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkUserConnects($ip)){
        throwException(CONNECTIONS_LIMIT_ERROR);
    }
    if (!registerConnect($ip,$link_existence['file_path'])){
        throwException(ALREADY_DOWNLOAD);
    }
    $speed = resolveDownloadSpeed();
    readFileWithSpeed('/'.$link_existence['file_path'],$name,$speed);
    deleteConnect($ip,$link_existence['file_path']);
}


function getFileByPath($path){
    $validator = Validator::getInstance();
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkUserConnects($ip)){
        throwException(CONNECTIONS_LIMIT_ERROR);
    }
    if (!registerConnect($ip,$path)){
        throwException(ALREADY_DOWNLOAD);
    }
    $speed = resolveDownloadSpeed();
    readFileWithSpeed('/'.$path,null,$speed);
    deleteConnect($ip,$path);
}


function saveTempLink($path,$link){
    $validator = Validator::getInstance();
    $link = $validator->Check('Md5Type',$link,[]);
    if ($link === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if (!saveLink($path,$link)){
        throwException(LINK_SAVE_ERROR);
    }
    return;
}


function generateFileId(){
    if (!empty($_FILES)) {
        $validator = Validator::getInstance();
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            throwException(DATA_FORMAT_ERROR);
        }
        if (!checkMime($_FILES['userfile']['tmp_name'],end(explode('.',$_FILES['userfile']['name'])))) {
            throwException(WRONG_FILE_TYPE);
        }
        $size = filesize($_FILES['userfile']['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            throwException(TOO_LARGE_FILE);
        }
        echo md5_file($_FILES['userfile']['tmp_name']);
        return;
    }
}


function createFile($file_name){
    if (!empty($_FILES)) {
        $validator = Validator::getInstance();
        $file_name = $validator->Check('Md5Type',$file_name,['types'=>FILES_ALLOWED_TYPES]);
        if ($file_name === false){
            throwException(DATA_FORMAT_ERROR);
        }
        $file_data = $validator->ValidateAllByMask($_FILES['userfile'], 'fileUploadMask');
        if ($file_data === false) {
            throwException(DATA_FORMAT_ERROR);
        }
        if (!$type = checkMime($_FILES['userfile']['tmp_name'],end(explode('.',$_FILES['userfile']['name'])))) {
            throwException(WRONG_FILE_TYPE);
        }
        $size = filesize($_FILES['userfile']['tmp_name']);
        if (($file_data["size"] > MAX_FILE_UPLOAD_SIZE) or ($size > MAX_FILE_UPLOAD_SIZE)) {
            throwException(TOO_LARGE_FILE);
        }
        $path = detectUploadPath();
        $fullpath = "$path/$file_name";
        if (upload($_FILES['userfile']['tmp_name'],$fullpath)){
            //addToZip();
            //makeThumbnail($file_name,$type);
            $url = Config::get('host_url');
            echo $url."/$fullpath";
            return;
        } else {
            throwException(FILE_CREATE_ERROR);
        }
    }
}


function deleteFile($path){
    $validator = Validator::getInstance();
    $file_name = $validator->Check('Path',$path,[]);
    if ($file_name === false){
        throwException(DATA_FORMAT_ERROR);
    }
    if (!unlink('/'.$path)){
        throwException(DELETE_FILE_ERROR);
    }
    return;
}



function moveFile($server,$path){
    $validator = Validator::getInstance();
    $server = $validator->Check('Path',$server,[]);
    if ($server === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $path = $validator->Check('Path',$path,[]);
    if ($path === false){
        throwException(DATA_FORMAT_ERROR);
    }
    $dest_server = @explode('//',$path)[0];
    $self = Config::get('host_url');
    if ($dest_server == $self){
        throwException(FILE_ALREADY_EXITS);
    }
    $name = @end(explode('/',$path));
    $response = SendFile($server."/createFile?file_name=$name",'/'.$path,$name);
    $file_path = $response;
    unlink('/'.$path);
    echo  $file_path;
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
    echo $size;
}


function getAllFiles(){
    $files = scandir(ROOTDIR.'/'.STORAGE);
    $result=[];
    foreach ($files as $file){
        if ($file != '.' and $file != '..'){
            $result[]=realpath(ROOTDIR.'/'.STORAGE.'/'.$file);
        }
    }
    //TODO no json
    echo json_encode($result);
}


