<?php


namespace Dreamstats\Service;


use Dreamstats\Model\ATGame;
use Dreamstats\Model\ATMatch;
use Dreamstats\Model\Event;
use Dreamstats\Model\Player;

class ATMatchService extends PdoService
{
    private const ORDER = ' ORDER BY a.id DESC, g.id ASC';
    private const SELECT_QUERY =
        'SELECT a.id              as match_id,
       a.event_id,
       e.name            as event_name,
       e.date            as event_date,
       a.player1_id,
       p1.nickname       as player1_nickname,
       p1.race           as player1_main_race,
       p1.country        as player1_country,
       p1.is_from_dreams as player1_is_from_dreams,
       a.player2_id,
       p2.nickname       as player2_nickname,
       p2.race           as player2_main_race,
       p2.country        as player2_country,
       p2.is_from_dreams as player2_is_from_dreams,
       a.opponent1_id,
       o1.nickname       as opponent1_nickname,
       o1.race           as opponent1_main_race,
       o1.country        as opponent1_country,
       o1.is_from_dreams as opponent1_is_from_dreams,
       a.opponent2_id,
       o2.nickname       as opponent2_nickname,
       o2.race           as opponent2_main_race,
       o2.country        as opponent2_country,
       o2.is_from_dreams as opponent2_is_from_dreams,
       g.id              as game_id,
       g.player1_race,
       g.player2_race,
       g.opponent1_race,
       g.opponent2_race,
       g.map,
       g.win
FROM at_match a
         INNER JOIN event e
                    ON a.event_id = e.id
         INNER JOIN player p1
                    ON a.player1_id = p1.id
         INNER JOIN player p2
                    ON a.player2_id = p2.id
         INNER JOIN player o1
                    ON a.opponent1_id = o1.id
         INNER JOIN player o2
                    ON a.opponent2_id = o2.id
         INNER JOIN at_game g
                    ON a.id = g.at_match_id
         ';

    public function insert(ATMatch $atMatch)
    {
        $query = "INSERT INTO at_match (event_id, player1_id, player2_id, opponent1_id, opponent2_id)
                  VALUES (:eventId, :player1Id, :player2Id, :opponent1Id, :opponent2Id)";
        $params = [
            'eventId' => $atMatch->event->id,
            'player1Id' => $atMatch->player1->id,
            'player2Id' => $atMatch->player2->id,
            'opponent1Id' => $atMatch->opponent1->id,
            'opponent2Id' => $atMatch->opponent2->id,
        ];
        $this->pdo->prepare($query)->execute($params);
        $atMatch->id = (int)$this->pdo->lastInsertId();
        $query = "INSERT INTO at_game (player1_race, player2_race, opponent1_race, opponent2_race, map, win, at_match_id)
                      VALUES (:player1Race, :player2Race, :opponent1Race, :opponent2Race, :map, :win, :atMatchId)";
        $statement = $this->pdo->prepare($query);
        foreach ($atMatch->games as $game) {
            $params = [
                'player1Race' => $game->p1Race,
                'player2Race' => $game->p2Race,
                'opponent1Race' => $game->op1Race,
                'opponent2Race' => $game->op2Race,
                'map' => $game->map,
                'win' => $game->win ? 'TRUE' : 'FALSE',
                'atMatchId' => $atMatch->id,
            ];
            $statement->execute($params);
            $game->id = $this->pdo->lastInsertId();
        }
    }

    public function delete(int $id) {
        $params = ['id' => $id];
        $this->pdo->prepare('DELETE FROM at_game WHERE at_match_id = :id')->execute($params);
        $this->pdo->prepare('DELETE FROM at_match WHERE id = :id')->execute($params);
    }

    /**
     * @param int $id
     * @return ATMatch[]
     */
    public function findByPlayer(int $id)
    {
        $query = self::SELECT_QUERY . 'WHERE p1.id = :id OR p2.id = :id OR o1.id = :id OR o2.id = :id' . self::ORDER;
        return $this->find($query, ['id' => $id]);
    }

    /**
     * @return ATMatch[]
     */
    public function findAll()
    {
        return $this->find(self::SELECT_QUERY . self::ORDER, []);
    }

    /**
     * @param $query
     * @param $params
     * @return ATMatch[]
     */
    private function find($query, $params)
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $records = $statement->fetchAll();

        $atMatches = [];
        foreach ($records as $row) {
            $id = $row['match_id'];
            if (isset($atMatches[$id])) {
                $atMatch = $atMatches[$id];
            } else {
                $event = new Event();
                $event->id = $row['event_id'];
                $event->name = $row['event_name'];
                $event->date = $row['event_date'];
                $player1 = $this->buildPlayer($row, 'player1');
                $player2 = $this->buildPlayer($row, 'player2');
                $opponent1 = $this->buildPlayer($row, 'opponent1');
                $opponent2 = $this->buildPlayer($row, 'opponent2');
                $atMatch = new ATMatch(
                    $id,
                    $event,
                    $player1,
                    $player2,
                    $opponent1,
                    $opponent2
                );
                $atMatches[$id] = $atMatch;
            }
            $atMatch->games[] = new ATGame(
                $row['game_id'],
                $row['player1_race'],
                $row['player2_race'],
                $row['opponent1_race'],
                $row['opponent2_race'],
                $row['map'],
                (bool)$row['win'],
            );
        }

        return $atMatches;
    }

    private function buildPlayer($row, $prefix)
    {
        $player = new Player();
        $player->id = $row[$prefix . '_id'];
        $player->nickname = $row[$prefix . '_nickname'];
        $player->race = $row[$prefix . '_main_race'];
        $player->country = $row[$prefix . '_country'];
        $player->isFromDreams = (bool)$row[$prefix . '_is_from_dreams'];
        return $player;
    }

}