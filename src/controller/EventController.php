<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class EventController extends DefaultController {

    /**
     * @var EventService
     */
    private $eventService;

    public function __construct($container) {
        parent::__construct($container);
        $this->eventService = $container['eventService'];
    }

    public function show(Request $req, Response $res) {

    }

    public function showRegisterPage(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        return $this->render($res, 'eventRegister.twig');
    }

    public function register(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $data = $req->getParsedBody();
        $event = new Event();
        $event->name = $data['name'];
        $event->date = $data['date'];

        $this->eventService->insert($event);

        $this->options['event'] = $event;
        return $this->render($res, 'eventRegister.twig');
    }

}