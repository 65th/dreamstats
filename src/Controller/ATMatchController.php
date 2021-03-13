<?php


namespace Dreamstats\Controller;


use Dreamstats\Model\ATGame;
use Dreamstats\Model\ATMatch;
use Dreamstats\Service\ATMatchService;
use Dreamstats\Service\EventService;
use Dreamstats\Service\PlayerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class ATMatchController extends DefaultController
{
    private ATMatchService $atMatchService;
    private PlayerService $playerService;
    private EventService $eventService;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->atMatchService = $container['atMatchService'];
        $this->playerService = $container['playerService'];
        $this->eventService = $container['eventService'];
    }

    public function showPlayerATMatches(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            return $response->withStatus(401);
        }
        $player = $this->playerService->findById($id);
        if (!$player) {
            return $response->withStatus(404);
        }
        $atMatches = $this->atMatchService->findByPlayer($id);
        $this->options += ['player' => $player, 'atMatches' => $atMatches];

        return $this->render($response, 'playerAtMatches.twig');
    }

    public function showAllAt(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->options += ['atMatches' => $this->atMatchService->findAll()];
        return $this->render($response, 'atMainPage.twig');
    }

    public function showAtMatchForm(ServerRequestInterface $request, ResponseInterface $response)
    {
        $forbidden = $this->forbidIfNotAdmin($response);
        if ($forbidden) return $forbidden;
        $this->options += [
            'events' => $this->eventService->findAll(),
            'players' => $this->playerService->findAll()
        ];
        return $this->render($response, 'atMatchNewForm.twig');
    }

    public function registerNewATMatch(ServerRequestInterface $request, Response $response)
    {
        $forbidden = $this->forbidIfNotAdmin($response);
        if ($forbidden) return $forbidden;

        $form = $request->getParsedBody();
        $atMatch = new ATMatch(
            0,
            $this->eventService->findById($form['eventId']),
            $this->playerService->findById($form['player1Id']),
            $this->playerService->findById($form['player2Id']),
            $this->playerService->findById($form['opponent1Id']),
            $this->playerService->findById($form['opponent2Id'])
        );
        foreach ($form['games'] as $game) {
            $atMatch->games[] = new ATGame(
                0,
                $game['player1Race'],
                $game['player2Race'],
                $game['opponent1Race'],
                $game['opponent2Race'],
                trim($game['map']) ?? '?',
                $game['win'] === 'true'
            );
        }
        $this->atMatchService->insert($atMatch);
        return $response->withStatus(201)->withJson($atMatch);
    }

    public function deleteAtMatch(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            return $response->withStatus(401);
        }
        $this->atMatchService->delete($id);
        return $response->withStatus(202);
    }
}