<?php
/**
 * Файл содержит константы, используемые для настроек приложения по умолчанию
 */

/**
 * An absolute path to apps directory
 */
define("ROOTDIR", dirname(__DIR__));

/**
 * Main settings file path
 */
define("SETTINGS_PATH", ROOTDIR."/config/config.ini");

/**
 * A list of helper function files to include
 */
define("HELPERS", [
    'main.php'
]);

define ("FILES_ALLOWED_TYPES",['jpg','jpeg','png','pdf','doc','docx','txt','diff']);

define ("ALLOWED_FILE_MIME_TYPES",['image/jpg','image/jpeg','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','text/plain']);

define("EX_TYPES",['diff']);

define('MAX_FILE_UPLOAD_SIZE', 100000000);