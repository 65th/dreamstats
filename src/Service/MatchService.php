<?php

namespace Dreamstats\Service;

use Dreamstats\Model\Event;
use Dreamstats\Model\Player;
use Dreamstats\Model\Score;
use Dreamstats\Model\TheMatch;

class MatchService extends PdoService
{
    public function deleteByEventId($eventId) {
        $statement = $this->pdo->prepare('DELETE FROM solo_match WHERE event_id = :eventId');
        $statement->execute([':eventId' => $eventId]);
    }

    public function insert(TheMatch $xxx)
    {
        $statement = $this->pdo->prepare("INSERT INTO solo_match (player_id, enemy_id, event_id, player_wins, enemy_wins)
                                          VALUES (:playerId, :enemyId, :eventId, :playerWins, :enemyWins)");
        $statement->execute([
            ":playerId" => $xxx->player->id,
            ":enemyId" => $xxx->enemy->id,
            ":eventId" => $xxx->event->id,
            ":playerWins" => $xxx->score->wins,
            ":enemyWins" => $xxx->score->loses
        ]);

        return $this->pdo->lastInsertId("solo_match_id_seq");
    }

    public function update(TheMatch $matchx)
    {
        $statement = $this->pdo->prepare("UPDATE solo_match SET player_id = :playerId, enemy_id = :enemyId,
                                          event_id = :eventId, player_wins = :playerWins, enemy_wins = :enemyWins
                                          WHERE id = :id");
        $statement->execute([
            ":id" => $matchx->id,
            ":playerId" => $matchx->player->id,
            ":enemyId" => $matchx->enemy->id,
            ":eventId" => $matchx->event->id,
            ":playerWins" => $matchx->score->wins,
            ":enemyWins" => $matchx->score->loses
        ]);
    }

    /**
     * @param $id
     * @return TheMatch
     */
    public function findById($id)
    {
        return $this->findWhere(new Player(), "m.ID = :id", [":id" => $id])[0];
    }

    /**
     * @param Player $player
     * @return TheMatch[]
     */
    public function findByPlayer(Player $player)
    {
        return $this->findWhere($player, "player_id = :id OR enemy_id = :id", [":id" => $player->id]);
    }

    /**
     * @param int $eventId
     * @return TheMatch[]
     */
    public function findByEventId(int $eventId) {
        return $this->findWhere(new Player(), 'event_id = :eventId', [':eventId' => $eventId], 'ASC');
    }

    /**
     * @param Player $player
     * @param Player $enemy
     * @return TheMatch[]
     */
    public function findByPlayers(Player $player, Player $enemy)
    {
        return $this->findWhere($player,
            "(player_id = :playerId AND enemy_id = :enemyId) OR (player_id = :enemyId AND enemy_id = :playerId)",
            [":playerId" => $player->id, ":enemyId" => $enemy->id]);
    }

    /**
     * @param Player $mainPlayer
     * @param $where
     * @param $bindings
     * @param string $order
     * @return TheMatch[]
     */
    private function findWhere(Player $mainPlayer, $where, $bindings, $order = 'DESC')
    {
        $matchesSql = "SELECT m.*, p.id AS player_id, p.nickname AS player_nickname, p.race AS player_race, p.country as player_country,
                           o.id AS enemy_id, o.nickname AS enemy_nickname, o.race AS enemy_race, o.country as enemy_country,
                           e.id as event_id, e.name as event_name, e.date as event_date
                           FROM solo_match m
                           INNER JOIN player p ON m.player_id = p.id
                           INNER JOIN player o ON m.enemy_id = o.id
                           INNER JOIN event e ON m.event_id = e.id
                           WHERE $where
                           ORDER BY m.id $order";

        $statement = $this->pdo->prepare($matchesSql);
        $statement->execute($bindings);

        $matches = [];
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            $matchx = new TheMatch();
            $matchx->id = $row['id'];
            if (!$mainPlayer->id || $row['player_id'] == $mainPlayer->id) {
                $matchx->player = $this->extractPlayer($row, "player_");
                $matchx->enemy = $this->extractPlayer($row, "enemy_");
                $matchx->score = new Score($row['player_wins'], $row['enemy_wins']);
            } else {
                $matchx->player = $this->extractPlayer($row, "enemy_");
                $matchx->enemy = $this->extractPlayer($row, "player_");
                $matchx->score = new Score($row['enemy_wins'], $row['player_wins']);
            }
            $matchx->event = new Event();
            $matchx->event->id = $row['event_id'];
            $matchx->event->name = $row['event_name'];
            $matchx->event->date = $row['event_date'];
            $matches[] = $matchx;
        }

        return $matches;
    }
}