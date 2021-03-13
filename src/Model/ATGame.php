<?php


namespace Dreamstats\Model;


class ATGame
{
    public int $id;
    public string $p1Race;
    public string $p2Race;
    public string $op1Race;
    public string $op2Race;
    public string $map;
    public bool $win;

    public function __construct(int $id, string $p1Race, string $p2Race, string $op1Race, string $op2Race, string $map, bool $win)
    {
        $this->id = $id;
        $this->p1Race = $p1Race;
        $this->p2Race = $p2Race;
        $this->op1Race = $op1Race;
        $this->op2Race = $op2Race;
        $this->map = $map;
        $this->win = $win;
    }
}