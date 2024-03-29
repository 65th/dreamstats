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
        $allies = [];
        foreach ($atMatches as $atMatch) {
            $atMatch->setMainPlayer($player->id);
            $allies[] = $atMatch->player2;
        }
        $allies = array_unique($allies, SORT_REGULAR);
        $this->options += ['player' => $player, 'atMatches' => $atMatches, 'allies' => $allies];

        return $this->render($response, 'playerAtMatches.twig');
    }

    public function showPairAtMatches(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $player = $this->playerService->findByName($args['name']);
        $ally = $this->playerService->findByName($args['ally']);
        if (!$player || !$ally) {
            return $response->withStatus(404);
        }
        $atMatches = $this->atMatchService->findByPair($player->id, $ally->id);
        foreach ($atMatches as $atMatch) {
            $atMatch->setMainPlayer($player->id);
        }
        $this->options += ['player' => $player, 'ally' => $ally, 'atMatches' => $atMatches];

        return $this->render($response, 'atPairMatches.twig');
    }

    public function showAllAt(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->options += ['atMatches' => $this->atMatchService->findAll()];
        return $this->render($response, 'atMainPage.twig');
    }

    public function showAtMatchForm(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $forbidden = $this->forbidIfNotAdmin($response);
        if ($forbidden) return $forbidden;

        $id = $args['id'] ?? null;
        if ($id) {
            $atMatch = $this->atMatchService->findById($id);
            if (!$atMatch) {
                return $response->withStatus(404);
            }
            $this->options['atMatch'] = $atMatch;
        }

        $this->options += [
            'events' => $this->eventService->findAll(),
            'players' => $this->playerService->findAll()
        ];
        return $this->render($response, 'atMatchNewForm.twig');
    }

    public function updateAtMatch(ServerRequestInterface $request, Response $response, $args) {
        $forbidden = $this->forbidIfNotAdmin($response);
        if ($forbidden) return $forbidden;

        $form = $request->getParsedBody();
        $atMatch = $this->atMatchService->findById($form['id']);
        if (!$atMatch) {
            return $response->withStatus(404);
        }
        if ($form['eventId'] != $atMatch->event->id) {
            $atMatch->event = $this->eventService->findById($form['eventId']);
        }
        if ($form['player1Id'] != $atMatch->player1->id) {
            $atMatch->player1 = $this->playerService->findById($form['player1Id']);
        }
        if ($form['player2Id'] != $atMatch->player2->id) {
            $atMatch->player2 = $this->playerService->findById($form['player2Id']);
        }
        if ($form['opponent1Id'] != $atMatch->opponent1->id) {
            $atMatch->opponent1 = $this->playerService->findById($form['opponent1Id']);
        }
        if ($form['opponent2Id'] != $atMatch->opponent2->id) {
            $atMatch->opponent2 = $this->playerService->findById($form['opponent2Id']);
        }
        $this->atMatchService->update($atMatch);

        return $response->withStatus(201)->withJson($atMatch);
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