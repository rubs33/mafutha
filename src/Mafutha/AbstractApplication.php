<?php
declare(strict_types=1);
namespace Mafutha;

/**
 * Abstract Application is responsible for:
 * - bootstrap environment (load config, register error/exception handler, etc.);
 * - dispatch apropriate controller/action.
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
abstract class AbstractApplication
{
    /**
     * Exit status
     */
    public const STATUS_SUCCESS          = 0;
    public const STATUS_ACTION_NOT_FOUND = 1;
    public const STATUS_ERROR            = 2;

    /**
     * Application config
     *
     * @var array
     */
    protected $config;

    /**
     * Bootstrap application
     *
     * @param array $config
     * @return $this
     */
    public function bootstrap(array $config): self
    {
        $this->config = $config;

        $this->registerErrorHandler()
            ->registerExceptionHandler()
            ->configureErrorControl()
            ->assertEnvironment();

        return $this;
    }

    /**
     * Register error handler
     *
     * @return $this
     */
    protected function registerErrorHandler(): self
    {
        if (isset($this->config['error_handler'])) {
            assert(
                is_callable($this->config['error_handler']),
                'Error handler must be callable'
            );
            $errorHandler = &$this->config['error_handler'];
        } else {
            $errorHandler = function($type, $message, $file, $line) {
                if (!($type & error_reporting())) {
                    return false;
                }
                throw new \ErrorException($message, 0, $type, $file, $line);
            };
        }
        set_error_handler($errorHandler);

        return $this;
    }

    /**
     * Register exception handler
     *
     * @return $this
     */
    protected function registerExceptionHandler(): self
    {
        if (isset($this->config['exception_handler'])) {
            assert(
                is_callable($this->config['exception_handler']),
                'Exception handler must be callable'
            );
        }

        return $this;
    }

    /**
     * Configure error_reporting and enable/disable showing errors
     * based on config
     *
     * @return $this
     */
    protected function configureErrorControl(): self
    {
        error_reporting($this->config['error_reporting']);
        if ($this->config['show_errors']) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            assert_options(ASSERT_ACTIVE, 1);
            assert_options(ASSERT_WARNING, 1);
        } else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            assert_options(ASSERT_ACTIVE, 0);
            assert_options(ASSERT_WARNING, 0);
        }
        if ($this->config['php_error_log_file']) {
            ini_set('error_log', $this->config['log_dir'] . DIRECTORY_SEPARATOR . $this->config['php_error_log_file']);
        }

        return $this;
    }

    /**
     * Assert that the Environment is ready to run the application
     *
     * @return void
     */
    protected function assertEnvironment()
    {
        if (!assert_options(ASSERT_ACTIVE)) {
            return;
        }

        // Cache dir
        assert(
            is_dir($this->config['cache_dir']) && is_writable($this->config['cache_dir']),
            'Cache dir does not exists or it is not writable'
        );

        // Log dir
        assert(
            is_dir($this->config['log_dir']) && is_writable($this->config['log_dir']),
            'Log dir does not exists or it is not writable'
        );
    }

    /**
     * Run the application
     *
     * @return int Exit status (AbstractApplication::STATUS_... constants)
     */
    abstract public function run(): int;

}