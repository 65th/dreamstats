<?php

abstract class PdoService {
    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    protected function extractPlayer($row, $prefix = "") {
        $player = new Player();
        $player->id = $row[$prefix . 'id'];
        $player->nickname = $row[$prefix . 'nickname'];
        $player->country = $row[$prefix . 'country'];
        $player->race = $row[$prefix . 'race'];

        return $player;
    }
}