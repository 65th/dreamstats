<?php

namespace Dreamstats\Service;

use Dreamstats\Model\Player;

class PlayerService extends PdoService
{
    /**
     * @param $onlyFromDreams
     * @return Player[]
     */
    public function findAll($onlyFromDreams = false)
    {
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

    public function findById($id)
    {
        $sql = "SELECT * FROM player WHERE id = :id";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $result = $statement->fetchAll();

        return $this->buildPlayer($result);
    }

    public function findByName($name) {
        $sql = 'SELECT * FROM player WHERE nickname = :nickname';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nickname' => $name]);
        $result = $stmt->fetchAll();

        return $this->buildPlayer($result);
    }

    public function insert(Player $player)
    {
        $sql = "INSERT INTO player (nickname, race, country, is_from_dreams)
                VALUES (:nickname, :race, :country, :isFromDreams)";
        $this->pdo->prepare($sql)->execute($this->resolvePlayerOptions($player));

        $player->id = $this->pdo->lastInsertId("player_id_seq");
    }

    public function update(Player $player)
    {
        $sql = "UPDATE player SET nickname = :nickname, race = :race, country = :country, is_from_dreams = :isFromDreams
                WHERE id = :id";
        $this->pdo->prepare($sql)->execute($this->resolvePlayerOptions($player));
    }

    private function resolvePlayerOptions(Player $player)
    {
        $options = [
            ":nickname" => $player->nickname,
            ":race" => $player->race,
            ":country" => $player->country,
            ":isFromDreams" => $player->isFromDreams ? "TRUE" : "FALSE"
        ];
        if ($player->id) {
            $options['id'] = $player->id;
        }

        return $options;
    }

    /**
     * @param array $result
     * @return Player|null
     */
    public function buildPlayer(array $result)
    {
        $player = null;
        if ($result) {
            $player = new Player();
            foreach ($result as $row) {
                $player = $this->extractPlayer($row);
            }
        }

        return $player;
    }
}