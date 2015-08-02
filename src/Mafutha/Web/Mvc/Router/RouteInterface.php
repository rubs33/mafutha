<?php
namespace Mafutha\Web\Mvc\Router;

/**
 * Route Interface
 *
 * @author Rubens Takiguti Ribeiro <rubs33@gmail.com>
 */
interface RouteInterface
{
    /**
     * Return weather the route match the web request
     *
     * @param \Mafutha\Web\Message\Request $request
     * @return bool
     */
    public function match(\Mafutha\Web\Message\Request $request);

    /**
     * Build an URL to current route based on options
     *
     * @param array $options
     * @return string
     */
    public function assemble(array $options);
}