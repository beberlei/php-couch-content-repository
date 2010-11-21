<?php

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    __DIR__ . "/../lib" . PATH_SEPARATOR .
    __DIR__ . "/../lib/vendor/object-freezer/");

spl_autoload_register(function($class) {
    if (strpos($class, "PHPContentRepository") === 0) {
        $file = str_replace("\\", "/", $class) . ".php";
        require($file);
    }
});

spl_autoload_register(function($class) {
    if (strpos($class, "Object") === 0) {
        $file = str_replace("_", "/", $class) . ".php";
        require(__DIR__ . "/../lib/vendor/object-freezer/" . $file);
    }
});