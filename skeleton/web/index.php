<?php

require(__DIR__ . '/../../vendor/autoload.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
chdir(__DIR__);

spl_autoload_register(function($class) {
    if (preg_match('#\Example\\\#', $class)) {
        require_once('../src/' . str_replace('\\', '/', $class) . '.php');
    }
});

exit(\Mafutha\Application::getInstance()->run());
