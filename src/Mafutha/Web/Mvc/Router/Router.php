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
     * Default URL application
     *
     * @var string
     */
    protected $webUrlBase = '';

    /**
     * Controller Namespace
     *
     * @var string
     */
    protected $controllerNamespace = '\\';

    /**
     * List of web routes, build as a tree
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
     * List of routes references to make easier to find a route in the tree
     *
     * @var array
     */
    protected $routesReferences = null;

    /**
     * Set the base application URL
     *
     * @param string $webUrlBase
     * @return $this
     */
    public function setWebUrlBase($webUrlBase)
    {
        $this->webUrlBase = $webUrlBase;
        return $this;
    }

    /**
     * Get the base application URL
     *
     * @return string
     */
    public function getWebUrlBase()
    {
        return $this->webUrlBase;
    }

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
     * Get controller namespace
     *
     * @return string
     */
    public function getControllerNamespace()
    {
        return $this->controllerNamespace;
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
     * @param \Psr\Http\Message\RequestInterface $request
     * @return array|null
     */
    public function findRoute(\Psr\Http\Message\RequestInterface $request)
    {
        $this->matchedRoute = [
            'controller' => null,
            'action'     => null,
            'params'     => [],
            'path'       => []
        ];
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
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $route
     * @return bool
     */
    protected function matchRoute(\Psr\Http\Message\RequestInterface $request, array $route)
    {
        return $this->matchPath($this->getRelativePathFromRequest($request), $route);
    }

    /**
     * Get the path of the request without the default path of default application url
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return string
     */
    protected function getRelativePathFromRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $requestPath = $request->getUri()->getPath();

        $defaultPath = parse_url($this->webUrlBase, PHP_URL_PATH);
        $defaultPathLength = strlen($defaultPath);

        if (substr_compare($requestPath, $defaultPath, 0, $defaultPathLength) === 0) {
            return substr($requestPath, $defaultPathLength);
        }
        return $requestPath;
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
     * @param string $routeName Route name
     * @param array $params Params used to build the URL
     * @param bool $useOptional Flag to prefer to use optional parts when possible, instead to omit them
     * @return string
     */
    public function buildUrl($routeName, array $params = [], $useOptional = false)
    {
        $route = $this->getRouteByName($routeName);

        // Only params that are different from the default are considered here
        foreach ($params as $param => $value) {
            if (isset($route['defaults'][$param]) && strval($value) === strval($route['defaults'][$param])) {
                unset($params[$param]);
            }
        }

        return $this->getWebUrlBase() . $this->buildUrlPath($routeName, $route['build'], $params, $route['defaults'], $useOptional, false);
    }

    /**
     * Build the path of the URL
     *
     * @param string $routeName Route name
     * @param array $parts Path parts
     * @param array $params Params used to build the URL
     * @param array $defaults Default values for params
     * @param bool $useOptional Flag to prefer to use optional parts when possible, instead to omit them
     * @param bool $isOptional Wheather these parts are optional or not
     * @return string
     */
    protected function buildUrlPath($routeName, array $parts, array $params, array $defaults, $useOptional, $isOptional)
    {
        $path = '';
        $hasUserValue = false;
        foreach ($parts as $part) {
            switch ($part['type']) {
                case 'literal':
                    $path .= $this->buildUrlPathLiteral($routeName, $part, $params, $defaults, $isOptional);
                    break;
                case 'optional':
                    if ($useOptional || $this->urlPartHasParams($part, $params)) {
                        $path .= $this->buildUrlPath($routeName, $part['value'], $params, $defaults, $useOptional, true);
                    }
                    break;
            }
        }
        return $path;
    }

    /**
     * Check wheather the URL part has a param (with value different from default)
     *
     * @param array $part
     * @param array $params
     * @return bool
     */
    protected function urlPartHasParams(array $part, array $params)
    {
        foreach ($part['params'] as $param => $format) {
            if (isset($params[$param])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build a literal part of the url path
     *
     * @param string $routeName Route name
     * @param array $part Path part
     * @param array $params Params used to build the URL
     * @param array $defaults Default values for params
     * @param bool $isOptional Wheather the part is optional or not
     * @return array
     */
    protected function buildUrlPathLiteral($routeName, array $part, array $params, array $defaults, $isOptional)
    {
        $tr = [];
        foreach ($part['params'] as $param => $defaultValue) {
            if (isset($params[$param])) {
                $tr['<' . $param . '>'] = $params[$param];
            } elseif (isset($defaults[$param])) {
                $tr['<' . $param . '>'] = $defaults[$param];
            } elseif (!$isOptional) {
                throw new \InvalidArgumentException(sprintf('Route "%s" requires param "%s"', $routeName, $param));
            }
        }
        return strtr($part['value'], $tr);
    }

    /**
     * Get the route data by its name
     *
     * @param string $routeName
     * @return array
     */
    public function getRouteByName($routeName)
    {
        if ($this->routesReferences === null) {
            $this->routesReferences = [];
            $this->buildRoutesReferences($this->routes);
        }

        assert(
            'array_key_exists($routeName, $this->routesReferences)',
            'Route name must be in the routes configuration file.'
        );

        return $this->routesReferences[$routeName];
    }

    /**
     * Build routes references on demand
     *
     * @param array $routes
     * @return void
     */
    protected function buildRoutesReferences(array &$routes)
    {
        foreach ($routes as $routeName => $route) {
            $this->routesReferences[$routeName] = &$routes[$routeName];

            if (isset($route['child_routes']) && $route['child_routes']) {
                $this->buildRoutesReferences($route['child_routes']);
            }
        }
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
