<?php
namespace Example\Controller\User;

class ListController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function showAction()
    {
        $this->getResponse()->getBody()->write('<p>list users page ' . $this->getRoute()['params']['page'] . '</p>');
    }
}