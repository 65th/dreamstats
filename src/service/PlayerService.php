<?php

class PlayerService extends PdoService {
    /**
     * @param $onlyFromDreams
     * @return Player[]
     */
    public function findAll($onlyFromDreams = false) {
        $sql = "SELECT * FROM player";
        if ($onlyFromDreams) {
            $sql .= " WHERE is_from_dreams = TRUE";
        }
        $sql .= " ORDER BY nickname";
        $result = $this->pdo->query($sql);
        $players = [];
        foreach ($result as $row) {
            $player = $this->extractPlayer($row, "");
            $players[] = $player;
        }

        return $players;
    }

    public function findById($id) {
        $sql = "SELECT * FROM player WHERE id = :id";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $result = $statement->fetchAll();

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
        $sql = "INSERT INTO player (nickname, race, country, is_from_dreams)
                VALUES (:nickname, :race, :country, :isFromDreams)";
        $this->pdo->prepare($sql)->execute([
            ":nickname" => $player->nickname,
            ":race" => $player->race,
            ":country" => $player->country,
            ":isFromDreams" => $player->isFromDreams ? "TRUE" : "FALSE"
        ]);
    }
}