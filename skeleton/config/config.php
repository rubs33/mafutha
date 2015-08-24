<?php
return [
    // Paths / Urls
    'web_url'    => 'http://localhost/mafutha',
    'web_dir'    => __DIR__ . '/../web',
    'app_dir'    => __DIR__ . '/../src',
    'cache_dir'  => __DIR__ . '/../cache',
    'log_dir'    => __DIR__ . '/../logs',
    'web_routes' => __DIR__ . '/webRoutes.txt',
    'cli_routes' => __DIR__ . '/cliRoutes.txt',

    // Errors
    'error_reporting'    => E_ALL,
    'show_errors'        => true,
#    'error_handler'      => ['\Example\ErrorHandler', 'catchError'],
#    'exception_handler'  => ['\Example\ExceptionHandler', 'catchException'],
    'php_error_log_file' => 'php_error.log',
    'error_route'        => ['controller' => 'Error', 'action' => 'error'],
    'not_found_route'    => ['controller' => 'Error', 'action' => 'notFound'],

    // General
    'controller_namespace' => '\\Example\\Controller\\',
    'default_cache_time'   => 3600, // 1 hour
];