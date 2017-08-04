<?php

abstract class DefaultController {
    /**
     * @var \Slim\Views\Twig
     */
    protected $view;

    public function __construct($container) {
        $this->view = $container['view'];
    }
}