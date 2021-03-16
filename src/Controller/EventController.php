<?php

namespace Dreamstats\Controller;

use Dreamstats\Model\Event;
use Dreamstats\Service\ATMatchService;
use Dreamstats\Service\EventService;
use Dreamstats\Service\MatchService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends DefaultController
{

    /**
     * @var EventService
     */
    private $eventService;
    private MatchService $matchService;
    private ATMatchService $atMatchService;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->eventService = $container['eventService'];
        $this->matchService = $container['matchService'];
        $this->atMatchService = $container['atMatchService'];
    }

    public function show(Request $req, Response $res)
    {
        $id = (int)$req->getAttribute('id');
        $event = $this->eventService->findById($id);
        if (!$event) {
            $res->getBody()->write('Not found');
            return $res->withStatus(404);
        }
        $this->options += [
            'event' => $event,
            'matches' => $this->matchService->findByEventId($id),
            'atMatches' => $this->atMatchService->findByEventId($id)
        ];
        return $this->render($res, 'event.twig');
    }

    public function showAll(Request $request, Response $response)
    {
        $this->options['events'] = $this->eventService->findAll();
        return $this->render($response, 'events.twig');
    }

    public function deleteEvent(Request $request, Response $response)
    {
        $forbidden = $this->forbidIfNotAdmin($response);
        if ($forbidden) return $forbidden;

        $id = (int)$request->getAttribute('id');
        $this->matchService->deleteByEventId($id);
        $this->atMatchService->deleteByEventId($id);
        $this->eventService->delete($id);
        return $response->withStatus(202);
    }

    public function showRegisterPage(Request $req, Response $res)
    {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        return $this->render($res, 'eventRegister.twig');
    }

    public function register(Request $req, Response $res)
    {
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