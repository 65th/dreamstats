<?php

class Statistics {

    /**
     * @var Match[]
     */
    private $matches;

    /**
     * Statistics constructor.
     * @param Match[] $matches
     */
    public function __construct($matches) {
        $this->matches = $matches;
    }

    public function get() {
        $statistics = [
            "byRace" => [
                "h" => [
                    "total" => new Score(0, 0),
                    "maps" => new Score(0, 0)
                ],
                "o" => [
                    "total" => new Score(0, 0),
                    "maps" => new Score(0, 0)
                ],
                "u" => [
                    "total" => new Score(0, 0),
                    "maps" => new Score(0, 0)
                ],
                "n" => [
                    "total" => new Score(0, 0),
                    "maps" => new Score(0, 0)
                ]
            ],
            "total" => new Score(0, 0),
            "maps" => new Score(0, 0)
        ];

        foreach ($this->matches as $match) {
            $score = $match->score;
            $statistics['maps']->wins += $score->wins;
            $statistics['maps']->loses += $score->loses;
            $statistics['byRace'][$match->enemy->race]['maps']->wins += $score->wins;
            $statistics['byRace'][$match->enemy->race]['maps']->loses += $score->loses;
            if ($score->wins > $score->loses) {
                $statistics['byRace'][$match->enemy->race]['total']->wins++;
                $statistics['total']->wins++;
            } else {
                $statistics['byRace'][$match->enemy->race]['total']->loses++;
                $statistics['total']->loses++;
            }
        }

        return $statistics;
    }
}