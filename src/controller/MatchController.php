<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class MatchController extends DefaultController {
    /**
     * @var PlayerService
     */
    private $playerService;
    /**
     * @var EventService
     */
    private $eventService;
    /**
     * @var MatchService
     */
    private $matchService;

    public function __construct($container) {
        parent::__construct($container);
        $this->playerService = $container['playerService'];
        $this->eventService = $container['eventService'];
        $this->matchService = $container['matchService'];
    }

    public function showRegisterForm(Request $req, Response $res) {
        $players = $this->playerService->findAll();
        $events = $this->eventService->findAll();

        return $this->view->render($res, 'matchNew.twig', ['players' => $players, 'events' => $events]);
    }

    public function registerApi(Request $req, Response $res) {
        $data = $req->getParsedBody();
        $match = new Match();
        $match->player = $this->playerService->findById($data['player']);
        $match->enemy = $this->playerService->findById($data['enemy']);
        $match->event = $this->eventService->findById($data['event']);
        $match->score = new Score($data['wins'], $data['loses']);

        $match->id = $this->matchService->insert($match);

        return $res->withJson(["match" => $match]);
    }

}