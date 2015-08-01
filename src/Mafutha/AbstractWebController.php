<?php

namespace Mafutha;

abstract class AbstractWebController
{
    protected $request;

    final public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
    }

    final public function getRequest()
    {
        return $this->request;
    }
}