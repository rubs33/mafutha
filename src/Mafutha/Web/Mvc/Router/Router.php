<?php
namespace Mafutha\Web\Mvc\Router;

/**
 * The Router class is responsible for match a web request with a route
 * or create a URL from a route.
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class Router
{
    /**
     * Controller Namespace
     *
     * @var string
     */
    protected $controllerNamespace = '\\';

    /**
     * List of web routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Matched route
     *
     * @var array
     */
    protected $matchedRoute = [];

    /**
     * Set controller namespace
     *
     * @var string $controllerNamespace
     * @return $this
     */
    public function setControllerNamespace($controllerNamespace)
    {
        $this->controllerNamespace = $controllerNamespace;
        return $this;
    }

    /**
     * Add a route to the list of routes
     *
     * @param string $name
     * @param array $route
     * @return $this
     */
    public function addRoute($name, array $route)
    {
        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * Find the route that matches the request
     *
     * @param \Mafutha\Web\Message\Request $request
     * @return RouteInterface|null
     */
    public function findRoute(\Mafutha\Web\Message\Request $request)
    {
        foreach ($this->routes as $name => $route) {
            $route['name'] = $name;
            if ($this->matchRoute($request, $route)) {
                return $this->matchedRoute;
            }
        }
    }

    /**
     * Check wheather a route match with an web request
     *
     * @param \Mafutha\Web\Message\Request $request
     * @param array $route
     * @return bool
     */
    public function matchRoute(\Mafutha\Web\Message\Request $request, array $route)
    {
        $this->matchedRoute = [
            'controller' => null,
            'action' => null,
            'params' => [],
            'path' => [],
        ];
        return $this->matchPath($request->getRelativePath(), $route);
    }

    /**
     * Match a path with a route
     *
     * @param string $path
     * @param array $route
     * @return bool
     */
    protected function matchPath($path, array $route)
    {
        if (!preg_match($route['regexp'], $path, $matches)) {
            return false;
        }
        $this->matchedRoute['path'][] = $route['name'];

        if (isset($matches['subpath'])) {
            $matched = false;
            if (isset($route['child_routes'])) {
                foreach ($route['child_routes'] as $childRouteName => $childRoute) {
                    $childRoute['name'] = $childRouteName;
                    if ($this->matchPath($matches['subpath'], $childRoute)) {
                        $matched = true;
                        break;
                    }
                }
            }
            if (!$matched) {
                array_pop($this->matchedRoute['path']);
                return false;
            }
        }

        foreach ($route['defaults'] as $key => $value) {
            switch ($key) {
                case 'controller':
                    if (isset($matches[$key])) {
                        $controllerClass = $this->normalizeController($matches[$key]);
                        if (!class_exists($controllerClass)) {
                            array_pop($this->matchedRoute['path']);
                            return false;
                        }
                        $this->matchedRoute[$key] = $controllerClass;
                    } else {
                        $controllerClass = $this->normalizeController($value);
                        $this->matchedRoute[$key] = $controllerClass;
                    }
                    break;
                case 'action':
                    if (isset($matches[$key])) {
                        $actionMethod = $this->normalizeAction($matches[$key]);
                        if (!(new \ReflectionClass($this->matchedRoute['controller']))->hasMethod($actionMethod)) {
                            array_pop($this->matchedRoute['path']);
                            return false;
                        }
                        $this->matchedRoute[$key] = $actionMethod;
                    } else {
                        $actionMethod = $this->normalizeAction($value);
                        $this->matchedRoute[$key] = $actionMethod;
                    }
                    break;
                default:
                    $this->matchedRoute['params'][$key] = isset($matches[$key]) ? $matches[$key] : $value;
                    break;
            }
        }

        return true;
    }

    /**
     * Build an URL based on a route definition
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function buildUrl($routeName, array $params = [])
    {
        assert(
            sprintf(
                'array_key_exists(%s, $this->routes)',
                var_export($routeName, true)
            ),
            'Route name must be a valid route.'
        );

        $route = $this->routes[$routeName];

        //TODO
    }

    /**
     * Normalize controller class by adding the namespace and the "Controller" suffix.
     *
     * @param string $controllerClass
     * @return string
     */
    public function normalizeController($controllerClass)
    {
        if (substr($controllerClass, 0, 1) !== '\\') {
            $controllerClass = $this->controllerNamespace . $controllerClass;
        }
        if (substr_compare($controllerClass, 'Controller', -10, 10) !== 0) {
            $controllerClass .= 'Controller';
        }
        return $controllerClass;
    }

    /**
     * Normalize action method by adding the "Action" suffix.
     *
     * @param string $actionMethod
     * @return void
     */
    public function normalizeAction($actionMethod)
    {
        if (substr_compare($actionMethod, 'Action', -6, 6) !== 0) {
            $actionMethod .= 'Action';
        }
        return $actionMethod;
    }
}
