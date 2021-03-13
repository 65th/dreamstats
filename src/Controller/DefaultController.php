<?php

namespace Dreamstats\Controller;

use Dreamstats\Model\User;
use Psr\Http\Message\ResponseInterface as Response;

abstract class DefaultController
{
    /**
     * @var \Slim\Views\Twig
     */
    protected $view;
    protected $options;

    public function __construct($container)
    {
        $this->view = $container['view'];
        $this->options = [
            'currentUser' => User::getCurrentUser()
        ];
    }

    protected function render(Response $response, $template)
    {
        return $this->view->render($response, $template, $this->options);
    }

    protected function forbidIfNotAdmin(Response $response)
    {
        if (!User::getCurrentUser()->admin) {
            return $response->withStatus(403)->write("Forbidden");
        }

        return null;
    }
}