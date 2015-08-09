<?php
/**
 * Example of application using Mafutha Framework.
 * The index.php is the script wich will receive all http requests.
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */

// Require the autoload created by composer
require(__DIR__ . '/../../vendor/autoload.php');

// Register autoload for the classes of this project
spl_autoload_register(function($class) {
    if (preg_match('#\Example\\\#', $class)) {
        $file = 'file://' . __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require_once($file);
        }
    }
});

// Run the application using the specified config and return the exit status
exit(
    (new \Mafutha\Web\Application())
        ->bootstrap(require('file://' . __DIR__ . '/../config/config.php'))
/*
// uncomment these lines if you are using PHP_SAPI == fpm-fcgi
        ->addHook(
            \Mafutha\Web\Application::AFTER_SEND_RESPONSE,
            function () {
                fastcgi_finish_request();
            }
        )
*/
        ->run()
);
