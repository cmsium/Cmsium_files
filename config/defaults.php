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

define('HOST_URL','files.local');
define("LINK_EXPIRED_TIME",3600);
define("CHUNK_SIZE",100000);
define ("FILES_ALLOWED_TYPES",['jpg','jpeg','png','pdf','doc','docx','txt']);
define ("ALLOWED_FILE_MIME_TYPES",['image/jpg','image/jpeg','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','text/plain']);
define('MAX_FILE_UPLOAD_SIZE', 100000000);