<?php
namespace Mafutha\Web\Mvc\Router;

/**
 * Abstract Web Route
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
abstract class AbstractRoute implements RouteInterface
{
    /**
     * Route name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Route specification
     *
     * @var string
     */
    protected $route = '';

    /**
     * Route options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Route defaults
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Set the route name
     *
     * @param strgin $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the route name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the route specification
     *
     * @param string $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get the route specification
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the route options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get the route options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the route defaults
     *
     * @param array $defaults
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Get the route defaults
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Magic method to create an instance of a route based on an array of data
     *
     * @param array $state
     * @return static
     */
    public static function __set_state($state)
    {
        $route = new static($state['name']);
        $route->setRoute($state['data']['route']);
        if (isset($state['data']['options'])) {
            $route->setOptions($state['data']['options']);
        }
        if (isset($state['data']['defaults'])) {
            $route->setDefaults($state['data']['defaults']);
        }
        return $route;
    }

}