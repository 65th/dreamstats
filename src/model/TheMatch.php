<?php

class TheMatch {
    /**
     * @var int
     */
    public $id;
    /**
     * @var Player
     */
    public $player;
    /**
     * @var Player
     */
    public $enemy;
    /**
     * @var Score
     */
    public $score;
    /**
     * @var Event
     */
    public $event;

    function __toString() {
        $template = "%s %d:%d %s @ %s";
        return sprintf($template, $this->player->nickname, $this->score->wins, $this->score->loses, $this->enemy->nickname, $this->event);
    }


}