<?php

namespace Dreamstats\Controller;

use Dreamstats\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController extends DefaultController
{

    public function showLoginPage(Request $req, Response $res)
    {
        $message = null;
        if (isset($_SESSION['loginMessage'])) {
            $message = $_SESSION['loginMessage'];
            unset($_SESSION['loginMessage']);
        }

        $this->options['message'] = $message;

        return $this->render($res, 'login.twig');
    }

    public function login(Request $req, Response $res)
    {
        $data = $req->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        if (!empty(User::$users[$username]) && password_verify($password, User::$users[$username]['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['loginMessage'] = "Logged in";
        } else {
            $_SESSION['loginMessage'] = "No such user or password is invalid";
        }

        return $res->withRedirect('/login');
    }

    public function logout(Request $req, Response $res)
    {
        session_destroy();
        session_start();
        $_SESSION['loginMessage'] = "Logged out";

        return $res->withRedirect('/login');
    }

}