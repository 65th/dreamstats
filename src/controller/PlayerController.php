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
        return $this->view->render($res, 'playerRegister.twig');
    }

    public function register(Request $req, Response $res) {
        $data = $req->getParsedBody();
        $player = new Player();
        $player->nickname = $data['nickname'];
        $player->race = $data['race'];
        $player->country = $data['country'];

        $this->playerService->insert($player);

        return $this->view->render($res, 'playerRegister.twig', ["player" => $player]);
    }
}