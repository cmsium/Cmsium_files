<?php

namespace Config;

use Config\Exceptions\ConfigNotFoundException;
use Config\Exceptions\ConfigNotReadableException;

class Config {

    public $parameters = [];
    public $moduleName;
    private $envPrefix;

    public function __construct($moduleName) {
        $this->moduleName = $moduleName;
        $this->fillPrefix();
        $this->readConfigFile();
    }

    public function get($paramName) {
        if ($envValue = $this->readEnvVariable($paramName)) {
            return $envValue;
        }
        return $this->parameters[$paramName];
    }

    private function readConfigFile() {
        $path = $this->detectFilePath();
        if (!$parsedFile = parse_ini_file($path)) {
            throw new ConfigNotReadableException;
        }
        $this->parameters = $parsedFile;
    }

    private function readEnvVariable($paramName) {
        $envParamName = $this->envPrefix.strtoupper($paramName);
        return getenv($envParamName);
    }

    private function detectFilePath() {
        $path = ConfigManager::$configPath."/{$this->moduleName}.custom.ini";
        if (is_file($path)) {
            return $path;
        }
        $defaultPath = ConfigManager::$configPath."/{$this->moduleName}.default.ini";
        if (is_file($defaultPath)) {
            return $defaultPath;
        } else {
            throw new ConfigNotFoundException;
        }
    }

    private function fillPrefix() {
        $this->envPrefix = strtoupper($this->moduleName).'_';
    }

}
