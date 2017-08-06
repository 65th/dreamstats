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
        $dreamsPlayers = $this->playerService->findAll(true);

        $this->options += ["player" => $player, "matches" => $matches, "dreamsPlayers" => $dreamsPlayers];

        return $this->render($res, 'player.twig');
    }

    public function showRegisterForm(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $this->options += ['countries' =>  Countries::all()];
        return $this->render($res, 'playerRegister.twig');
    }

    public function register(Request $req, Response $res) {
        $forbidden = $this->forbidIfNotAdmin($res);
        if ($forbidden) return $forbidden;

        $data = $req->getParsedBody();
        $player = new Player();
        $player->nickname = $data['nickname'];
        $player->race = $data['race'];
        $player->country = $data['country'];
        $player->isFromDreams = !empty($data['isFromDreams']);

        $this->playerService->insert($player);

        $this->options += ["player" => $player, "countries" => Countries::all()];

        return $this->render($res, 'playerRegister.twig');
    }

    public function compare(Request $req, Response $res) {
        $player = $this->playerService->findById($req->getAttribute("playerId"));
        $enemy = $this->playerService->findById($req->getAttribute("enemyId"));
        $matches = $this->matchService->findByPlayers($player, $enemy);
        $statistics = new Statistics($matches);

        $this->options += ["player" => $player, "enemy" => $enemy, "matches" => $matches, "statistics" => $statistics->get()];

        return $this->render($res, "playerCompare.twig");
    }
}