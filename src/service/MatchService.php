<?php

class MatchService extends PdoService {
    public function insert(Match $matchx) {
        $statement = $this->pdo->prepare("INSERT INTO solo_match (player_id, enemy_id, event_id, player_wins, enemy_wins)
                                          VALUES (:playerId, :enemyId, :eventId, :playerWins, :enemyWins)");
        $statement->execute([
            ":playerId" => $matchx->player->id,
            ":enemyId" => $matchx->enemy->id,
            ":eventId" => $matchx->event->id,
            ":playerWins" => $matchx->score->wins,
            ":enemyWins" => $matchx->score->loses
        ]);

        return $this->pdo->lastInsertId("solo_match_id_seq");
    }

    public function update(Match $matchx) {
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

    public function findById($id) {
        return $this->findWhere(new Player(), "m.ID = :id", [":id" => $id])[0];
    }

    public function findByPlayer(Player $player) {
        return $this->findWhere($player, "player_id = :id OR enemy_id = :id", [":id" => $player->id]);
    }


    public function findByPlayers(Player $player, Player $enemy) {
        return $this->findWhere($player,
            "(player_id = :playerId AND enemy_id = :enemyId) OR (player_id = :enemyId AND enemy_id = :playerId)",
            [":playerId" => $player->id, ":enemyId" => $enemy->id]);
    }

    private function findWhere(Player $mainPlayer, $where, $bindings) {
        $matchesSql = "SELECT m.*, p.id AS player_id, p.nickname AS player_nickname, p.race AS player_race, p.country as player_country,
                           o.id AS enemy_id, o.nickname AS enemy_nickname, o.race AS enemy_race, o.country as enemy_country,
                           e.id as event_id, e.name as event_name, e.date as event_date
                           FROM solo_match m
                           INNER JOIN player p ON m.player_id = p.id
                           INNER JOIN player o ON m.enemy_id = o.id
                           INNER JOIN event e ON m.event_id = e.id
                           WHERE $where
                           ORDER BY m.id DESC";

        $statement = $this->pdo->prepare($matchesSql);
        $statement->execute($bindings);

        $matches = [];
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            $matchx = new Match();
            $matchx->id = $row['id'];
            if ($row['player_id'] == $mainPlayer->id) {
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