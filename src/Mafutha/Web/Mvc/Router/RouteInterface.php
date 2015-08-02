<?php
namespace Mafutha\Web\Mvc\Router;

interface RouteInterface
{
    public function match(\Mafutha\Web\Message\Request $request);

    public function assemble(array $options);
}