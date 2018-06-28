<?php
/**
 * Файл содержит константы, используемые для настроек приложения по умолчанию
 */

/**
 * Константа устанавливает абсолютный путь к корневой директории проекта
 */
define("ROOTDIR", dirname(__DIR__));
/**
 * Константа для определения пути к настройкам по умолчанию
 */
define("SETTINGS_PATH", ROOTDIR."/config/config.ini");
define("STORAGE",'storage');
define("LINK_EXPIRED_TIME",3600);
define("CHUNK_SIZE",1000000);
define("MAX_CONNECTIONS_PER_IP",5);
define("FILES_ALLOWED_TYPES",['jpg','jpeg','png','pdf','doc','docx','txt','diff']);
define("ALLOWED_FILE_MIME_TYPES",['image/jpg','image/jpeg','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','text/plain']);
define("EX_TYPES",['diff']);
define('MAX_FILE_UPLOAD_SIZE', 100000000);
define('FILES_PREVIEW_SIZE',100);
define('THUMBNAIL_PATH',ROOTDIR.'/images');

//github test comment