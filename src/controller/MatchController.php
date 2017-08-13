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
        $match = $this->matchService->findById($matchId);

        $players = $this->playerService->findAll();
        $events = $this->eventService->findAll();

        $this->options += [
            'match' => $match,
            'players' => $players,
            'events' => $events
        ];

        return $this->render($res, 'matchNew.twig');
    }

    public function registerApi(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $data = $req->getParsedBody();
        $match = new Match();
        $match->id = !empty($data['id']) ? $data['id'] : null;
        $match->player = $this->playerService->findById($data['player']);
        $match->enemy = $this->playerService->findById($data['enemy']);
        $match->event = $this->eventService->findById($data['event']);
        $match->score = new Score($data['wins'], $data['loses']);

        if ($match->id) {
            $this->matchService->update($match);
        } else {
            $match->id = $this->matchService->insert($match);
        }

        return $res->withJson(["match" => $match]);
    }

}