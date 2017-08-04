<?php

class PlayerService extends PdoService {
    /**
     * @param bool $includeStatistics
     * @return Player[]
     */
    public function findAll($includeStatistics = false) {
        $result = $this->pdo->query("SELECT * FROM player ORDER BY nickname");
        $players = [];
        foreach ($result as $row) {
            $player = new Player;
            $player->id = $row['id'];
            $player->nickname = $row['nickname'];
            $player->race = $row['race'];
            $player->country = $row['country'];
            $players[] = $player;
        }

        return $players;
    }

    public function findById($id) {
        $sql = "SELECT * FROM player WHERE id = $id";
        $result = $this->pdo->query($sql);

        $player = null;
        if ($result) {
            $player = new Player();
            foreach ($result as $row) {
                $player = $this->extractPlayer($row);
            }
        }

        return $player;
    }

    public function insert(Player $player) {
        $sql = "INSERT INTO player (nickname, race, country) VALUES ('$player->nickname', '$player->race', '$player->country')";
        $this->pdo->query($sql);
    }
}