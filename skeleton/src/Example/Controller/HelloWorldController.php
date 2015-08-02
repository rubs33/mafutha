<?php

namespace Example\Controller;

class HelloWorldController extends \Mafutha\Web\Mvc\Controller\AbstractController
{
    public function showAction() {
        echo 'Hello world!';
    }
}