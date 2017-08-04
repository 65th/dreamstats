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
        return $this->view->render($res, 'eventRegister.twig');
    }

    public function register(Request $req, Response $res) {
        $data = $req->getParsedBody();
        $event = new Event();
        $event->name = $data['name'];
        $event->date = $data['date'];

        $this->eventService->insert($event);

        return $this->view->render($res, 'eventRegister.twig', ['event' => $event]);
    }

}