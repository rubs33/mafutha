<?php

namespace Example\Controller;

class UserController extends \Mafutha\AbstractWebController
{
    public function listAction() {
        echo 'user list';
    }

    public function viewAction() {
        echo 'user view';
    }

    public function editAction() {
        echo 'user edit';
    }

    public function insertAction() {
        echo 'user insert';
    }
}