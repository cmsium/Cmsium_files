<?php

function loadRecursive($path, $name) {
    $items = glob($path.DIRECTORY_SEPARATOR."*");

    foreach($items as $item) {
    $isPhp = (isset(pathinfo($item)["extension"]) && pathinfo($item)["extension"] === "php");

    if (is_file($item) && $isPhp && (basename($item) == "$name.php")) {
          include $item;
        } elseif (is_dir($item)) {
          loadRecursive($item, $name);
        }
    }
}

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/validation', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/router', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/config', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/http_server', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/plumber', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/errors', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/webgear', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/utils/openapi', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/utils/migrator', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/database', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/files', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/config', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/validation', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/http_server', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/webgear', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/presenter', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/transaction', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/queue', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/errors', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive(dirname(__DIR__).'/core/lib/testgear', $className);
});

