<?php
namespace Mafutha\Web;

class Application extends \Mafutha\AbstractApplication
{
    protected $request;
    protected $router;
    protected $route;

    /**
     * Microtime when the application finished the main execution
     * @var float
     */
    protected $finishTime;

    /**
     * {@inheritdoc}
     *
     * @return int Exit status
     */
    public function run()
    {
        $this->loadRouter();
        $this->loadRequest();

        $this->route = $this->router->findRoute($this->request);
        if ($this->route === null) {
            //TODO show 404
            return self::STATUS_ACTION_NOT_FOUND;
        }

        $this->callAction();

        $this->finishTime = microtime(true);
#debug
printf('<p>Time: %0.7f</p>', $this->finishTime - $_SERVER['REQUEST_TIME_FLOAT']);

        return self::STATUS_SUCCESS;
    }

    public function callAction()
    {
        $controllerClass = $this->route->getDefaults()['controller'];
        $actionMethod    = $this->route->getDefaults()['action'];

        if (substr($controllerClass, 0, 1) !== '\\') {
            $controllerClass = $this->config['controller_namespace'] . $controllerClass;
        }
        if (substr_compare($controllerClass, 'Controller', -10, 10) !== 0) {
            $controllerClass .= 'Controller';
        }
        if (substr_compare($actionMethod, 'Action', -6, 6) !== 0) {
            $actionMethod .= 'Action';
        }

        // Assert Controller
        assert(
            sprintf(
                '(new \ReflectionClass(%s))->isSubclassOf(%s)',
                var_export($controllerClass, true),
                var_export(\Mafutha\Web\Mvc\Controller\AbstractController::class, true)
            ),
            'Controller must be an instanceof ' . \Mafutha\Web\Mvc\Controller\AbstractController::class
        );

        // Assert Action
        assert(
            sprintf(
                '(new \ReflectionClass(%s))->hasMethod(%s)',
                var_export($controllerClass, true),
                var_export($actionMethod, true)
            ),
            'Controller must have the "' . $actionMethod . '" method'
        );

        // Create controller and call action
        $controller = new $controllerClass();
        $controller->setRequest($this->request);
        $controller->setRoute($this->route);
        call_user_func([$controller, $actionMethod]);
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function loadRouter()
    {
        $routes = require($this->config['web_routes']);

        $this->router = new \Mafutha\Web\Mvc\Router\Router();
        foreach ($routes as $name => $route) {
            if (is_array($route)) {
                $class = sprintf('\\Mafutha\\Web\\Mvc\\Router\\%sRoute', ucfirst($route['type']));

                $route = $class::__set_state(['name' => $name, 'data' => $route]);
            }
            $this->router->addRoute($name, $route);
        }
    }

    public function getRequest()
    {
        if ($this->request === null) {
            $this->loadRequest();
        }
        return $this->request;
    }

    protected function loadRequest()
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

        $this->request = new \Mafutha\Web\Message\Request(
            $_SERVER['REQUEST_METHOD'],
            $url,
            $headers,
            file_get_contents('php://input'),
            substr($_SERVER['SERVER_PROTOCOL'], 5)
        );
        $this->request->setBasePath($this->getBasePath());
    }

    protected function getBasePath()
    {
        return parse_url($this->config['base_url'], PHP_URL_PATH);
    }
}