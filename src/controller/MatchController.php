<?php
use Slim\Http\Request;
use Slim\Http\Response;

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
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $players = $this->playerService->findAll();
        $events = $this->eventService->findAll();

        $this->options += [
            'players' => $players,
            'events' => $events
        ];

        return $this->render($res, 'matchNew.twig');
    }

    public function showEditForm(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $matchId = $req->getAttribute('id');
        $matchx = $this->matchService->findById($matchId);

        $players = $this->playerService->findAll();
        $events = $this->eventService->findAll();

        $this->options += [
            'match' => $matchx,
            'players' => $players,
            'events' => $events
        ];

        return $this->render($res, 'matchNew.twig');
    }

    public function registerApi(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $data = $req->getParsedBody();
        $matchx = new Match();
        $matchx->id = !empty($data['id']) ? $data['id'] : null;
        $matchx->player = $this->playerService->findById($data['player']);
        $matchx->enemy = $this->playerService->findById($data['enemy']);
        $matchx->event = $this->eventService->findById($data['event']);
        $matchx->score = new Score($data['wins'], $data['loses']);

        if ($matchx->id) {
            $this->matchService->update($matchx);
        } else {
            $matchx->id = $this->matchService->insert($matchx);
        }

        return $res->withJson(["match" => $matchx]);
    }

}