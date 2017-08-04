CREATE TABLE player
(
  id       INTEGER PRIMARY KEY NOT NULL,
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