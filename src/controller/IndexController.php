<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class IndexController extends DefaultController {

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


    public function index(Request $req, Response $res) {
        $players = $this->playerService->findAll();
        foreach ($players as $player) {
            $matches = $this->matchService->findByPlayer($player);
            $stats = new Statistics($matches);
            $player->statistics = $stats->get();
        }
        return $this->view->render($res, 'index.twig', ['players' => $players]);
    }
}