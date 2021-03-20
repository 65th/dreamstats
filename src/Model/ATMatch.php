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

    public function setMainPlayer(int $mainPlayerId) {
        if ($mainPlayerId === $this->player1->id) {
            return;
        }
        if ($mainPlayerId === $this->player2->id) {
            $tmp = $this->player1;
            $this->player1 = $this->player2;
            $this->player2 = $tmp;
            foreach ($this->games as $game) {
                $tmpr = $game->p1Race;
                $game->p1Race = $game->p2Race;
                $game->p2Race = $tmpr;
            }
        }
        if (in_array($mainPlayerId, [$this->opponent1->id, $this->opponent2->id])) {
            $tmp = $this->player1;
            $tmp2 = $this->player2;
            $this->player1 = $this->opponent1;
            $this->player2 = $this->opponent2;
            $this->opponent1 = $tmp;
            $this->opponent2 = $tmp2;
            foreach ($this->games as $game) {
                $game->win = !$game->win;
                $tmpr1 = $game->p1Race;
                $tmpr2 = $game->p2Race;
                $game->p1Race = $game->op1Race;
                $game->p2Race = $game->op2Race;
                $game->op1Race = $tmpr1;
                $game->op2Race = $tmpr2;
            }
            $this->setMainPlayer($mainPlayerId);
        }
    }
}