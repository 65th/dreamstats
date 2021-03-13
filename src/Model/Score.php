<?php

namespace Dreamstats\Model;
class Score
{
    public $wins = 0;
    public $loses = 0;

    public function __construct($wins, $loses)
    {
        $this->wins = $wins;
        $this->loses = $loses;
    }

    public function isZatasheno() {
        return $this->wins > $this->loses;
    }
}