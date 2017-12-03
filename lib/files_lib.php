<?php

/**Move file from temporary place to storage
 * @param $upload_path
 * @return bool Move status
 */
function upload($tmp_path,$upload_path){
    $result = move_uploaded_file($tmp_path, $upload_path);
    if (!$result)
        return false;
    chmod($upload_path,0775);
    return true;

}

function detectUploadPath(){
    return ROOTDIR.'/'.STORAGE;
}

/**
 * Generate file id
 * @return string File id
 */
function generateId($path){
    $file_id = md5_file($path);
    return $file_id;
}

/**
 * Check is it a real image;
 * @param string $path Path to image
 */
function checkImage($path){
    $check = getimagesize($path);
    if($check === false) {
        return false;
    }
    return filesize($path);
}

function checkMime($path,$exception=null){
    if ($exception and in_array($exception,EX_TYPES))
        return true;
    $type = mime_content_type($path);
    if (!in_array($type,ALLOWED_FILE_MIME_TYPES))
        return false;
    return $type;
}

function readFileWithSpeed($path,$filename, $speed = false){
    $filesize = filesize($path);
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
    header('Last-Modified: ' . gmdate('r', filemtime($path)));
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    $file = fopen($path, 'rb');
    fseek($file, $from);
    $size = $to - $from;
    $downloaded = 0;
    while(!feof($file) and ($downloaded<$size)) {
        echo fread($file, !$speed?CHUNK_SIZE:$speed);
        flush();
        ob_flush();
        if ($speed)
            sleep(1);
        $downloaded += !$speed?CHUNK_SIZE:$speed;
    }
    fclose($file);
}


function registerConnect($ip,$file_path){
    $conn = DBConnection::getInstance();
    $query = "CALL addConnect('$ip','$file_path');";
    return  $conn->performQuery($query)?$ip:false;
}

function checkUserConnects($ip){
    $conn = DBConnection::getInstance();
    $query = "CALL checkConnects('$ip');";
    return  ($conn->performQueryFetch($query)['count(*)'] + 1) <= MAX_CONNECTIONS_PER_IP;
}

function deleteConnect($ip,$file_path){
    $conn = DBConnection::getInstance();
    $query = "CALL deleteConnect('$ip','$file_path');";
    return  $conn->performQuery($query);
}

function resolveDownloadSpeed(){
    $bandwidth = Config::get('bandwidth');
    $conn = DBConnection::getInstance();
    $query = "CALL countConnects();";
    $connects_count = $conn->performQueryFetch($query)['count(*)'];
    if ($connects_count == 0)
        return $bandwidth > CHUNK_SIZE ? CHUNK_SIZE : $bandwidth;
    $speed = (int)$bandwidth/$connects_count;
    return $speed > CHUNK_SIZE ? CHUNK_SIZE : $speed;
}

/**
 * Render and output system file with basic headers
 *
 * @param string $path Path to requested file
 * @param string $name File output name
 */
function renderSysFile($path,$name){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    ob_clean();
    readfile($path);
}


function makeThumbnail($path,$file_type){
    try {
        switch ($file_type) {
            case 'application/pdf':
                $path = $path . "[0]";
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/msword':
                return true;
            default:
                break;
        }
        $image = new Imagick($path);
        $image->setImageFormat('png');
        $image->thumbnailImage(FILES_PREVIEW_SIZE, 0);
        $path = @end(explode('/', $path));
        $result = $image->writeImage(THUMBNAIL_PATH.$path.".png");
        return $result;
    } catch(Exception $e){
        echo $e->getMessage();
    }
}


function renderThumbnail($path,$name){
    $path = @end(explode('/',$path));
    $thumb_path = THUMBNAIL_PATH.$path.".png";
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($thumb_path));
    ob_clean();
    //flush();
    readfile($thumb_path);
}


/**Check file permissions according to roles
 *
 * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
 * @return mixed Check status
 */
function checkFileRoles($action,$file_id,$user)
{
    $conn = DBConnection::getInstance();
    $query = "CALL checkFilePermissions('$file_id','$user');";
    $permissions = $conn->performQueryFetchAll($query);
    if (!$permissions)
        return false;
    return checkActionRoles($action, $permissions);
}


/**Check request action congruence to file permissions
 *
 * @param string $action Requested file action ('r' - read/'c' - create/'d' - delete)
 * @param int $rights File permissions
 * @return mixed Check status
 */
function checkActionRoles($action,$permissions){
    switch ($action){
        case 'd': $bit = 0; break;//delete
        case 'r': $bit = 1; break;//read
        case 'c': $bit = 2; break;//create
        default: return 0;
    }
    return ($permissions >> $bit) & 1;
}


/**
 * Get temporary link by file
 *
 * @return mixed Temporary link(hashed string)|false
 */
function getLink($path){
    $conn = DBConnection::getInstance();
    $query = "CALL getFileLink('$path');";
    return  $conn->performQueryFetch($query);
}


function getFileByLink($link){
    $conn = DBConnection::getInstance();
    $query = "CALL getFile('$link');";
    return  $conn->performQueryFetch($query);
}

function saveLink($path,$link){
    $conn = DBConnection::getInstance();
    $exptime = date("Y-m-d H:i:s",time() + LINK_EXPIRED_TIME);
    $query = "CALL saveFileLink('$path','$link','$exptime');";
    return  $conn->performQuery($query);
}

function checkIntegrity($file_id,$path){
    return ($file_id == md5_file($path));
}

function testEcho($msg){
    echo "Hello $msg";
}