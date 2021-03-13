CREATE TABLE public.at_match
(
    id serial NOT NULL,
    event_id integer NOT NULL,
    player1_id integer NOT NULL,
    player2_id integer NOT NULL,
    opponent1_id integer NOT NULL,
    opponent2_id integer NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT atm_p1_fk FOREIGN KEY (player1_id)
        REFERENCES public.player (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
    CONSTRAINT atm_p2_fk FOREIGN KEY (player2_id)
        REFERENCES public.player (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
    CONSTRAINT atm_o1_fk FOREIGN KEY (opponent1_id)
        REFERENCES public.player (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
    CONSTRAINT atm_o2_fk FOREIGN KEY (opponent2_id)
        REFERENCES public.player (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID,
    CONSTRAINT atm_event_fk FOREIGN KEY (event_id)
        REFERENCES public.event (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);

CREATE TABLE public.at_game
(
    id serial NOT NULL,
    player1_race character varying NOT NULL,
    player2_race character varying NOT NULL,
    opponent1_race character varying NOT NULL,
    opponent2_race character varying NOT NULL,
    map character varying NOT NULL,
    win boolean NOT NULL,
    at_match_id integer,
    PRIMARY KEY (id),
    CONSTRAINT at_match_fk FOREIGN KEY (at_match_id)
        REFERENCES public.at_match (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        NOT VALID
);