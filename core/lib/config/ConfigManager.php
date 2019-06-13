<?php

namespace Config;

class ConfigManager {

    protected static $instances;
    public static $configPath = ROOTDIR.'/config';

    public static function setConfigPath($configPath) {
        self::$configPath = $configPath;
    }

    public static function module($moduleName) {
        if (isset(self::$instances[$moduleName])) {
            $instance = self::$instances[$moduleName];
        } else {
            self::$instances[$moduleName] = new Config($moduleName);
            $instance = self::$instances[$moduleName];
        }
        return $instance;
    }

}