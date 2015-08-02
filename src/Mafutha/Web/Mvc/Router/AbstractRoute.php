<?php
namespace Mafutha\Web\Mvc\Router;

abstract class AbstractRoute implements RouteInterface
{
    protected $name = '';
    protected $route = '';
    protected $options = [];
    protected $defaults = [];

    public function __construct($name)
    {
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

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