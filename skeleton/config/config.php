<?php
return [
    // Paths / Urls
    'base_url'   => 'http://localhost/mafutha',
    'base_path'  => __DIR__ . '/../web',
    'app_path'   => __DIR__ . '/../src',
    'web_routes' => __DIR__ . '/webRoutes.php',
    'cli_routes' => __DIR__ . '/cliRoutes.php',

    // Errors
    'error_reporting'   => E_ALL,
    'show_errors'       => true,
#    'error_handler'     => ['\Example\ErrorHandler', 'catchError'],
#    'exception_handler' => ['\Example\ExceptionHandler', 'catchException'],

    // General
    'controller_namespace' => '\\Example\\Controller\\',
];