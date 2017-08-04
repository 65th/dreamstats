<?php

class MatchService extends PdoService {
    public function insert(Match $match) {
        $statement = $this->pdo->prepare("INSERT INTO solo_match (player_id, enemy_id, event_id, player_wins, enemy_wins)
                                          VALUES (:playerId, :enemyId, :eventId, :playerWins, :enemyWins)");
        $statement->execute([
           ":playerId" => $match->player->id,
            ":enemyId" => $match->enemy->id,
            ":eventId" => $match->event->id,
            ":playerWins" => $match->score->wins,
            ":enemyWins" => $match->score->loses
        ]);

        return $this->pdo->lastInsertId("solo_match_id_seq");
    }

    public function findByPlayer(Player $player) {
        $matchesSql = "SELECT m.*, p.id AS player_id, p.nickname AS player_nickname, p.race AS player_race, p.country as player_country,
                           o.id AS enemy_id, o.nickname AS enemy_nickname, o.race AS enemy_race, o.country as enemy_country,
                           e.id as event_id, e.name as event_name, e.date as event_date
                           FROM solo_match m
                           INNER JOIN player p ON m.player_id = p.id
                           INNER JOIN player o ON m.enemy_id = o.id
                           INNER JOIN event e ON m.event_id = e.id
                           WHERE player_id = 1 OR enemy_id = 1
                           ORDER BY m.id DESC";

        $statement = $this->pdo->prepare($matchesSql);
        $statement->execute([]);

        $matches = [];
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            $match = new Match();
            $match->id = $row['id'];
            $match->player = $player;
            if ($row['player_id'] == $player->id) {
                $match->enemy = $this->extractPlayer($row, "enemy_");
                $match->score = new Score($row['player_wins'], $row['enemy_wins']);
            } else {
                $match->enemy = $this->extractPlayer($row, "player_");
                $match->score = new Score($row['enemy_wins'], $row['player_wins']);
            }
            $match->event = new Event();
            $match->event->id = $row['event_id'];
            $match->event->name = $row['event_name'];
            $match->event->date = $row['event_date'];
            $matches[] = $match;
        }

        return $matches;
    }
}