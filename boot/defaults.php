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

define("TEST_HELPERS", [
    'test.main.php'
]);

define("CLEAN_EXPIRED_LINKS_TIME", 1000*60);
define("CLEAN_DELETED_FILES_LINKS_TIME", 1000*60);

define("SERVER_STATUS", true);