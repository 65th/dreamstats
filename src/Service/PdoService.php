<?php

namespace Dreamstats\Service;

use Dreamstats\Model\Player;
use PDO;

abstract class PdoService
{
    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function extractPlayer($row, $prefix = "")
    {
        $player = new Player();
        $player->id = $row[$prefix . 'id'];
        $player->nickname = $row[$prefix . 'nickname'];
        $player->country = $row[$prefix . 'country'];
        $player->race = $row[$prefix . 'race'];
        $player->isFromDreams = !empty($row[$prefix . 'is_from_dreams']) && $row[$prefix . 'is_from_dreams'];

        return $player;
    }
}