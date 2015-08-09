<?php
namespace Mafutha\Web\Mvc\Controller;

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
    public function setRequest(\Psr\Http\Message\RequestInterface $request)
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
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the HTTP response
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return $this
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
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
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the Route that matched the controller/action
     *
     * @param array $route
     * @return $this
     */
    public function setRoute(array $route)
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
    public function getRoute()
    {
        return $this->route;
    }

}