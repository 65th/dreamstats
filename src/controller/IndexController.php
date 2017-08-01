<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class IndexController {
    private $container;
    public function __construct($container) {
        $this->container = $container;
    }

    public function index(Request $req, Response $res) {

    }
}