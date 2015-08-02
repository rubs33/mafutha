<?php
namespace Mafutha\Web\Mvc\Router;

/**
 * The Router class is responsible for receive a list of routes
 * and can match a web request with a route.
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
class Router
{
    /**
     * List of web routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Add a route to the list of routes
     *
     * @param string $name
     * @param RouteInterface $route
     * @return $this
     */
    public function addRoute($name, RouteInterface $route)
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
        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }
    }
}