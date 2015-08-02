<?php
namespace Mafutha\Web\Mvc\Router;

class Router
{
    protected $routes = [];

    public function addRoute($name, RouteInterface $route)
    {
        $this->routes[$name] = $route;
    }

    public function findRoute(\Mafutha\Web\Message\Request $request)
    {
        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }
    }
}