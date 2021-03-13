CREATE TABLE player
(
  id       SERIAL PRIMARY KEY NOT NULL,
  nickname VARCHAR(20)         NOT NULL,
  race     VARCHAR             NOT NULL,
  country  VARCHAR(2)          NOT NULL
);
CREATE UNIQUE INDEX player_nickname_uindex ON player (nickname);

CREATE TABLE public.event
(
  id   SERIAL PRIMARY KEY NOT NULL,
  name VARCHAR(100)       NOT NULL,
  date DATE               NOT NULL
);
CREATE UNIQUE INDEX event_name_uindex ON public.event (name);

CREATE TABLE public.solo_match
(
  id          SERIAL PRIMARY KEY NOT NULL,
  player_id   INT                NOT NULL,
  enemy_id    INT                NOT NULL,
  player_wins INT                NOT NULL,
  enemy_wins  INT                NOT NULL,
  event_id    INT                NOT NULL,
  CONSTRAINT solo_match__player_fk1 FOREIGN KEY (player_id) REFERENCES player (id),
  CONSTRAINT solo_match__enemy_fk FOREIGN KEY (enemy_id) REFERENCES player (id),
  CONSTRAINT solo_match__event_fk FOREIGN KEY (event_id) REFERENCES event (id)
);
CREATE UNIQUE INDEX solo_match_id_uindex ON public.solo_match (id);

ALTER TABLE public.player ADD is_from_dreams BOOLEAN DEFAULT FALSE NOT NULL;

SELECT a.id              as match_id,
       a.event_id,
       e.name            as event_name,
       e.date            as event_date,
       a.player1_id,
       p1.nickname       as player1_nickname,
       p1.race           as player1_main_race,
       p1.country        as player1_country,
       p1.is_from_dreams as player1_is_from_dreams,
       a.player2_id,
       p2.nickname       as player2_main_nickname,
       p2.race           as player2_race,
       p2.country        as player12_country,
       p2.is_from_dreams as player2_is_from_dreams,
       a.opponent1_id,
       o1.nickname       as opponent1_nickname,
       o1.race           as opponent1_main_race,
       o1.country        as opponent1_country,
       o1.is_from_dreams as opponent1_is_from_dreams,
       a.opponent2_id,
       o2.nickname       as opponent2_nickname,
       o2.race           as opponent2_main_race,
       o2.country        as opponent2_country,
       o2.is_from_dreams as opponent2_is_from_dreams,
       g.id              as game_id,
       g.player1_race,
       g.player2_race,
       g.opponent1_race,
       g.opponent2_race,
       g.map,
       g.win
FROM at_match a
         INNER JOIN event e
                    ON a.event_id = e.id
         INNER JOIN player p1
                    ON a.player1_id = p1.id
         INNER JOIN player p2
                    ON a.player2_id = p2.id
         INNER JOIN player o1
                    ON a.opponent1_id = o1.id
         INNER JOIN player o2
                    ON a.opponent2_id = o2.id
         INNER JOIN at_game g
                    ON a.id = g.at_match_id;