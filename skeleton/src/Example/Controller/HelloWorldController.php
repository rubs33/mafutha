<?php
namespace Example\Controller;

class HelloWorldController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function showAction() {
        $this->response->getBody()->write('<p>Hello world!</p>');
    }
}