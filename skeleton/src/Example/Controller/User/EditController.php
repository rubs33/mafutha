<?php
namespace Example\Controller\User;

class EditController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function formAction()
    {
        $this->getResponse()->getBody()->write('<p>Edit user ' . $this->getRoute()['params']['id'] . ' (form)</p>');
    }

    public function saveAction()
    {
        $this->getResponse()->getBody()->write('<p>Edit user ' . $this->getRoute()['params']['id'] . ' (save)</p>');
    }
}
