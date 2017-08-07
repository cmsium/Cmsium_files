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
define("LINK_EXPIRED_TIME",3600);
define("CHUNK_SIZE",100000);
