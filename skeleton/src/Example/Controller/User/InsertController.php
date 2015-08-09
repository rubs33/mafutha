<?php
namespace Example\Controller\User;

class InsertController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function formAction()
    {
        $this->getResponse()->getBody()->write('<p>Insert user (form)</p>');
    }

    public function saveAction()
    {
        $this->getResponse()->getBody()->write('<p>Insert user (save)</p>');
    }
}