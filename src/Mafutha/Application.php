<?php
namespace Mafutha;

final class Application
{
    const STATUS_SUCCESS       = 0;
    const STATUS_INVALID_ROUTE = 1;

    protected static $instance;
    protected $config;
    protected $webRequest;
    protected $webRoute;
    protected $finishTime;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->config = require('../config/config.php');
    }

    private function __clone() {}

    public function run()
    {
        switch (PHP_SAPI) {
            default:
                $status = $this->runWeb();
            break;
            case 'cli':
                $status = $this->runCli();
            break;
        }
        $this->finishTime = microtime(true);

//TODO Debug
printf('<p>%0.7f</p>', $this->finishTime - $_SERVER['REQUEST_TIME_FLOAT']);
        return $status;
    }

    protected function runWeb()
    {
        // Find Route
        $this->webRequest = $this->getWebRequest();
        $this->webRoute = $this->findWebRouteFromRequest($this->webRequest);
        if ($this->webRoute === null) {
            return self::STATUS_INVALID_ROUTE;
        }
        $controllerClass = $this->webRoute['controller'];
        $action = $this->webRoute['action'];

        // Assert Route
        assert(
            sprintf(
                '(new \ReflectionClass(%s))->isSubclassOf(%s)',
                var_export($controllerClass, true),
                var_export(AbstractWebController::class, true)
            ),
            'Controller is an instanceof ' . AbstractController::class
        );

        // Invoke action
        $controller = new $controllerClass();
        $controller->setRequest($this->webRequest);
        call_user_func([$controller, $action]);

        return self::STATUS_SUCCESS;
    }

    protected function runCli()
    {
        //TODO
    }

    public function getWebRequest()
    {
        if ($this->webRequest === null) {
            $this->webRequest = $this->loadWebRequest();
        }
        return $this->webRequest;
    }

    protected function loadWebRequest()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr_compare($key, 'HTTP_', 0, 5) === 0) {
                $httpKey = str_replace('_', '-', substr($key, 5));
                $headers[$httpKey] = $value;
            }
        }

        $url = sprintf(
            '%s://%s%s%s%s%s',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@' : '',
            $_SERVER['HTTP_HOST'],
            isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : 80,
            $_SERVER['REQUEST_URI'],
            $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''
        );

        $request = new \GuzzleHttp\Psr7\Request(
            $_SERVER['REQUEST_METHOD'],
            $url,
            $headers,
            file_get_contents('php://input'),
            substr($_SERVER['SERVER_PROTOCOL'], 5)
        );
        return $request;
    }

    protected function findWebRouteFromRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $urlParts = parse_url($this->config['wwwroot']);
        if (isset($urlParts['path'])) {
            $defaultPath = $urlParts['path'];
            $defaultPathLength = strlen($defaultPath);
        } else {
            $defaultPath = null;
            $defaultPathLength = 0;
        }

        $routes = require('../config/webRoutes.php');
        foreach ($routes as $route) {
            $path = $request->getUri()->getPath();
            if ($defaultPathLength && substr($path, 0, $defaultPathLength) == $defaultPath) {
                $path = substr($path, $defaultPathLength);
            }
            if (preg_match($route['regex'], $path, $matches)) {
                if (isset($matches['controller'])) {
                    $route['controller'] = $matches['controller'];
                }
                if (isset($matches['action'])) {
                    $route['action'] = $matches['action'];
                }
                $route['matches'] = $matches;
                return $route;
            }
        }
    }
}