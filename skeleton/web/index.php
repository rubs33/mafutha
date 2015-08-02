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
        require_once('../src/' . str_replace('\\', '/', $class) . '.php');
    }
});

// Run the application using the specified config and return the exit status
exit(
    (new \Mafutha\Web\Application())
        ->bootstrap(require(__DIR__ . '/../config/config.php'))
        ->run()
);
