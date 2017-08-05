<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PlayerController extends DefaultController {
    /**
     * @var PlayerService
     */
    private $playerService;
    /**
     * @var MatchService
     */
    private $matchService;


    public function __construct($container) {
        parent::__construct($container);
        $this->playerService = $container['playerService'];
        $this->matchService = $container['matchService'];
    }

    public function show(Request $req, Response $res) {
        $id = $req->getAttribute('id');
        $player = $this->playerService->findById($id);
        $matches = $this->matchService->findByPlayer($player);
        $statistics = new Statistics($matches);
        $player->statistics = $statistics->get();

        return $this->view->render($res, 'player.twig', ["player" => $player, "matches" => $matches]);
    }

    public function showRegisterForm(Request $req, Response $res) {
        return $this->view->render($res, 'playerRegister.twig', ['countries' =>  Countries::all()]);
    }

    public function register(Request $req, Response $res) {
        $data = $req->getParsedBody();
        $player = new Player();
        $player->nickname = $data['nickname'];
        $player->race = $data['race'];
        $player->country = $data['country'];
        $player->isFromDreams = !empty($data['isFromDreams']);

        $this->playerService->insert($player);

        return $this->view->render($res, 'playerRegister.twig', ["player" => $player, "countries" => Countries::all()]);
    }

    public function compare(Request $req, Response $res) {
        $player = $this->playerService->findById($req->getAttribute("playerId"));
        $enemy = $this->playerService->findById($req->getAttribute("enemyId"));
        $matches = $this->matchService->findByPlayers($player, $enemy);
        $statistics = new Statistics($matches);

        return $this->view->render($res, "playerCompare.twig", ["player" => $player, "enemy" => $enemy, "matches" => $matches, "statistics" => $statistics->get()]);
    }
}