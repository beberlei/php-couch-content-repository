<?php

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/../lib");

spl_autoload_register(function($class) {
    $file = str_replace("\\", "/", $class) . ".php";
    require($file);
});