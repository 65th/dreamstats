<?php


namespace Dreamstats\Model;


class ATMatch
{
    public int $id;
    /**
     * @var Event
     */
    public Event $event;
    /**
     * @var Player
     */
    public Player $player1;
    /**
     * @var Player
     */
    public Player $player2;
    /**
     * @var Player
     */
    public Player $opponent1;
    /**
     * @var Player
     */
    public Player $opponent2;
    /**
     * @var ATGame[]
     */
    public array $games = [];

    public function __construct(int $id, Event $event, Player $player1, Player $player2, Player $opponent1, Player $opponent2)
    {
        $this->id = $id;
        $this->event = $event;
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->opponent1 = $opponent1;
        $this->opponent2 = $opponent2;
    }

    public function calcScore()
    {
        $p = 0;
        $o = 0;
        foreach ($this->games as $game) {
            $game->win ? $p++ : $o++;
        }
        return new Score($p, $o);
    }
}