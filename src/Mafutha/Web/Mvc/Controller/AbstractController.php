<?php
declare(strict_types=1);
namespace Mafutha\Web\Mvc\Controller;

use Psr\Http\Message\ {
    RequestInterface,
    ResponseInterface
};
use Mafutha\Web\Mvc\Router\Router;

/**
 * Abstract controller for Web requests
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
abstract class AbstractController
{
    /**
     * HTTP request
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * HTTP response
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Router object that match requests an build URL
     *
     * @var \Mafutha\Web\Mvc\Router\Router
     */
    protected $router;

    /**
     * Route that matched the controller/action
     *
     * @var array
     */
    protected $route;

    /**
     * Set the HTTP request
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request): self
    {
        if (!is_null($this->request)) {
            throw new \LogicException('The request was already setted');
        }
        $this->request = $request;

        return $this;
    }

    /**
     * Get the HTTP request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Set the HTTP response
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response): self
    {
        if (!is_null($this->response)) {
            throw new \LogicException('The response was already setted');
        }
        $this->response = $response;

        return $this;
    }

    /**
     * Get the HTTP response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the Router
     *
     * @param \Mafutha\Web\Mvc\Router\Router $router
     * @return $this
     */
    public function setRouter(Router $router): self
    {
        if (!is_null($this->router)) {
            throw new \LogicException('The router was already setted');
        }
        $this->router = $router;

        return $this;
    }

    /**
     * Get the Router
     *
     * @return \Mafutha\Web\Mvc\Router\Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Set the Route that matched the controller/action
     *
     * @param array $route
     * @return $this
     */
    public function setRoute(array $route): self
    {
        if (!is_null($this->route)) {
            throw new \LogicException('The route was already setted');
        }
        $this->route = $route;

        return $this;
    }

    /**
     * Get the Route that matched the controller/action
     *
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route;
    }

}