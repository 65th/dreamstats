<?php

namespace Dreamstats\Controller;

use Dreamstats\Model\Statistics;
use Dreamstats\Service\MatchService;
use Dreamstats\Service\PlayerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends DefaultController
{

    public static function hey() {
        die('hello');
    }

    /**
     * @var PlayerService
     */
    private $playerService;
    /**
     * @var MatchService
     */
    private $matchService;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->playerService = $container['playerService'];
        $this->matchService = $container['matchService'];
    }


    public function index(Request $req, Response $res)
    {
        $players = $this->playerService->findAll(true);
        foreach ($players as $player) {
            $matches = $this->matchService->findByPlayer($player);
            $stats = new Statistics($matches);
            $player->statistics = $stats->get();
        }

        $this->options['players'] = $players;

        return $this->render($res, 'index.twig');
    }
}