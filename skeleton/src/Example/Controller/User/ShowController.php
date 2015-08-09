<?php
namespace Example\Controller\User;

class ShowController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function showAction()
    {
        $this->getResponse()->getBody()->write('<p>Show user ' . $this->getRoute()['params']['id'] . '</p>');
    }
}
