<?php
namespace Mafutha\Web;

/**
 * The Web Application is responsible for:
 * - load http request;
 * - load router;
 * - find the apropriate route for http request;
 * - dispatch apropriate controller/action
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class Application extends \Mafutha\AbstractApplication
{
    /**
     * HTTP request
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Router is responsible for load application routes and detect
     * apropriate route for an http request
     *
     * @var \Mafutha\Web\Mvc\Router\Router
     */
    protected $router;

    /**
     * Route that match the http request by router
     *
     * @var \Mafutha\Web\Mvc\Router\RouteInterface
     */
    protected $route;

    /**
     * Microtime when the application finished the main execution
     *
     * @var float
     */
    protected $finishTime;

    /**
     * {@inheritdoc}
     *
     * @return int Exit status (AbstractApplication::STATUS_... constants)
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
printf('<p>Included files: %d</p>', count(get_included_files()));

        return self::STATUS_SUCCESS;
    }

    /**
     * Call the action's route
     *
     * @return void
     */
    public function callAction()
    {
        $controllerClass = $this->route->getDefaults()['controller'];
        $actionMethod    = $this->route->getDefaults()['action'];

        $this->normalizeControllerAction($controllerClass, $actionMethod);

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

    /**
     * Normalize controller class by adding the namespace and the "Controller" suffix.
     * Normalize action method by adding the "Action" suffix.
     *
     * @param string $controllerClass
     * @param string $actionMethod
     * @return void
     */
    protected function normalizeControllerAction(&$controllerClass, &$actionMethod)
    {
        if (substr($controllerClass, 0, 1) !== '\\') {
            $controllerClass = $this->config['controller_namespace'] . $controllerClass;
        }
        if (substr_compare($controllerClass, 'Controller', -10, 10) !== 0) {
            $controllerClass .= 'Controller';
        }
        if (substr_compare($actionMethod, 'Action', -6, 6) !== 0) {
            $actionMethod .= 'Action';
        }
    }

    /**
     * Get the Web Router
     *
     * @return \Mafutha\Web\Mvc\Router\Router
     */
    public function getRouter()
    {
        if ($this->router === null) {
            $this->loadRouter();
        }
        return $this->router;
    }

    /**
     * Load the Router by adding the web routes to it
     *
     * @return $this
     */
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

        return $this;
    }

    /**
     * Get the HTTP Request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->loadRequest();
        }
        return $this->request;
    }

    /**
     * Load the HTTP Request with its data
     *
     * @return $this
     */
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

        // Set base path of application, to hint router
        $this->request->setBasePath(parse_url($this->config['base_url'], PHP_URL_PATH));

        return $this;
    }

}