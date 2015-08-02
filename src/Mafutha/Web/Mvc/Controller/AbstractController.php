<?php
namespace Mafutha\Web\Mvc\Controller;

/**
 * Abstract controller for Web requests
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
abstract class AbstractController
{
    /**
     * HTTP request
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Route that matched the controller/action
     * @var \Mafutha\Web\Mvc\Router\RouteInterface
     */
    protected $route;

    /**
     * Set the HTTP request
     * @param \Psr\Http\Message\RequestInterface $request
     * @return self
     */
    final public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        if (!is_null($this->request)) {
            throw new \LogicException('The request was already setted');
        }
        $this->request = $request;
        return $this;
    }

    /**
     * Get the HTTP request
     * @return \Psr\Http\Message\RequestInterface
     */
    final public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the Route that matched the controller/action
     * @param \Mafutha\Web\Mvc\Router\RouteInterface $route
     * @return self
     */
    final public function setRoute(\Mafutha\Web\Mvc\Router\RouteInterface $route)
    {
        if (!is_null($this->route)) {
            throw new \LogicException('The route was already setted');
        }
        $this->route = $route;
        return $this;
    }

    /**
     * Get the Route that matched the controller/action
     * @return array
     */
    final public function getRoute()
    {
        return $this->route;
    }
}