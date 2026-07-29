--
-- PostgreSQL database dump
--

\restrict QuZv8BW24gQ9ZwnFYRVmkvPXSZxNZTGm7N07hDgxSeY1TdPXVa3RQoq5vazjYX6

-- Dumped from database version 16.10
-- Dumped by pg_dump version 16.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: aeroporti; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.aeroporti (
    id integer NOT NULL,
    iata_code character varying(10),
    icao_code character varying(10),
    nome character varying(200),
    citta character varying(100),
    nazione character varying(100),
    iso_nazione character varying(10),
    latitudine numeric(9,6),
    longitudine numeric(9,6),
    altitudine integer DEFAULT 0,
    tipo character varying(50),
    militare boolean DEFAULT false,
    attivo boolean DEFAULT true
);


--
-- Name: aeroporti_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.aeroporti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: aeroporti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.aeroporti_id_seq OWNED BY public.aeroporti.id;


--
-- Name: localita; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.localita (
    id bigint NOT NULL,
    codice character varying(40),
    nome character varying(200) NOT NULL,
    citta character varying(200) NOT NULL,
    nazione character varying(100) NOT NULL,
    iso_nazione character varying(10),
    latitudine numeric(9,6) NOT NULL,
    longitudine numeric(9,6) NOT NULL,
    popolazione bigint,
    tipo character varying(50) DEFAULT 'localita'::character varying NOT NULL,
    fonte character varying(50),
    attivo boolean DEFAULT true NOT NULL,
    creato_il timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT localita_latitudine_check CHECK (((latitudine >= ('-90'::integer)::numeric) AND (latitudine <= (90)::numeric))),
    CONSTRAINT localita_longitudine_check CHECK (((longitudine >= ('-180'::integer)::numeric) AND (longitudine <= (180)::numeric)))
);


--
-- Name: localita_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.localita_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: localita_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.localita_id_seq OWNED BY public.localita.id;


--
-- Name: log_calcoli; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_calcoli (
    id integer NOT NULL,
    soggetto_id integer,
    tipo character varying(10),
    parametri jsonb,
    risultato jsonb,
    durata_ms integer,
    creato_il timestamp without time zone DEFAULT now()
);


--
-- Name: log_calcoli_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.log_calcoli_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: log_calcoli_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.log_calcoli_id_seq OWNED BY public.log_calcoli.id;


--
-- Name: preferiti; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.preferiti (
    id integer NOT NULL,
    soggetto_id integer,
    nome_luogo character varying(100),
    nazione character varying(10),
    latitudine numeric(9,6),
    longitudine numeric(9,6),
    altitudine integer DEFAULT 0,
    note text,
    creato_il timestamp without time zone DEFAULT now(),
    utente_id integer
);


--
-- Name: preferiti_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.preferiti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: preferiti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.preferiti_id_seq OWNED BY public.preferiti.id;


--
-- Name: sessioni_rl; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessioni_rl (
    id integer NOT NULL,
    soggetto_id integer,
    sessione_rs_id integer,
    numero_mese integer NOT NULL,
    data_rl timestamp without time zone NOT NULL,
    data_rl_gmt timestamp without time zone NOT NULL,
    luogo_rl character varying(100),
    nazione_rl character varying(10),
    latitudine numeric(9,6),
    longitudine numeric(9,6),
    altitudine integer DEFAULT 0,
    timezone_rl character varying(50),
    condizione character varying(50),
    stelline numeric(3,1),
    val_stringa character varying(100),
    note text,
    creato_il timestamp without time zone DEFAULT now(),
    utente_id integer,
    anno_rs integer
);


--
-- Name: sessioni_rl_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sessioni_rl_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sessioni_rl_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sessioni_rl_id_seq OWNED BY public.sessioni_rl.id;


--
-- Name: sessioni_rs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessioni_rs (
    id integer NOT NULL,
    soggetto_id integer,
    anno integer NOT NULL,
    data_rs timestamp without time zone NOT NULL,
    data_rs_gmt timestamp without time zone NOT NULL,
    luogo_rs character varying(100),
    nazione_rs character varying(10),
    latitudine numeric(9,6),
    longitudine numeric(9,6),
    altitudine integer DEFAULT 0,
    timezone_rs character varying(50),
    condizione character varying(50) DEFAULT 'Decima'::character varying,
    stelline numeric(3,1),
    val_stringa character varying(100),
    note text,
    creato_il timestamp without time zone DEFAULT now(),
    utente_id integer
);


--
-- Name: sessioni_rs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sessioni_rs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sessioni_rs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sessioni_rs_id_seq OWNED BY public.sessioni_rs.id;


--
-- Name: soggetti; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.soggetti (
    id integer NOT NULL,
    codice character varying(20),
    nome character varying(100) NOT NULL,
    data_nascita date NOT NULL,
    ora_nascita time without time zone NOT NULL,
    ora_nascita_gmt time without time zone NOT NULL,
    luogo_nascita character varying(100),
    nazione_nascita character varying(10),
    latitudine numeric(9,6),
    longitudine numeric(9,6),
    altitudine integer DEFAULT 0,
    timezone character varying(50),
    offset_gmt numeric(5,2),
    daylight numeric(5,2) DEFAULT 0,
    note text,
    creato_il timestamp without time zone DEFAULT now(),
    modificato_il timestamp without time zone DEFAULT now(),
    residenza_luogo character varying(200),
    residenza_latitudine numeric(10,6),
    residenza_longitudine numeric(10,6),
    residenza_nazione character varying(100),
    utente_id integer DEFAULT 1 NOT NULL
);


--
-- Name: soggetti_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.soggetti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: soggetti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.soggetti_id_seq OWNED BY public.soggetti.id;


--
-- Name: utenti; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.utenti (
    id integer NOT NULL,
    username character varying(60) NOT NULL,
    email character varying(200) DEFAULT ''::character varying NOT NULL,
    password_hash character varying(255) NOT NULL,
    ruolo character varying(20) DEFAULT 'astrologo'::character varying NOT NULL,
    attivo boolean DEFAULT true NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    ultimo_accesso timestamp with time zone,
    nome_completo character varying(200),
    telefono character varying(50),
    note text,
    CONSTRAINT utenti_ruolo_check CHECK (((ruolo)::text = ANY (ARRAY[('admin'::character varying)::text, ('astrologo'::character varying)::text])))
);


--
-- Name: utenti_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.utenti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: utenti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.utenti_id_seq OWNED BY public.utenti.id;


--
-- Name: aeroporti id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.aeroporti ALTER COLUMN id SET DEFAULT nextval('public.aeroporti_id_seq'::regclass);


--
-- Name: localita id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localita ALTER COLUMN id SET DEFAULT nextval('public.localita_id_seq'::regclass);


--
-- Name: log_calcoli id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_calcoli ALTER COLUMN id SET DEFAULT nextval('public.log_calcoli_id_seq'::regclass);


--
-- Name: preferiti id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.preferiti ALTER COLUMN id SET DEFAULT nextval('public.preferiti_id_seq'::regclass);


--
-- Name: sessioni_rl id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rl ALTER COLUMN id SET DEFAULT nextval('public.sessioni_rl_id_seq'::regclass);


--
-- Name: sessioni_rs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rs ALTER COLUMN id SET DEFAULT nextval('public.sessioni_rs_id_seq'::regclass);


--
-- Name: soggetti id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.soggetti ALTER COLUMN id SET DEFAULT nextval('public.soggetti_id_seq'::regclass);


--
-- Name: utenti id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti ALTER COLUMN id SET DEFAULT nextval('public.utenti_id_seq'::regclass);


--
-- Name: aeroporti aeroporti_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.aeroporti
    ADD CONSTRAINT aeroporti_pkey PRIMARY KEY (id);


--
-- Name: localita localita_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.localita
    ADD CONSTRAINT localita_pkey PRIMARY KEY (id);


--
-- Name: log_calcoli log_calcoli_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_calcoli
    ADD CONSTRAINT log_calcoli_pkey PRIMARY KEY (id);


--
-- Name: preferiti preferiti_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.preferiti
    ADD CONSTRAINT preferiti_pkey PRIMARY KEY (id);


--
-- Name: sessioni_rl sessioni_rl_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rl
    ADD CONSTRAINT sessioni_rl_pkey PRIMARY KEY (id);


--
-- Name: sessioni_rs sessioni_rs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rs
    ADD CONSTRAINT sessioni_rs_pkey PRIMARY KEY (id);


--
-- Name: soggetti soggetti_codice_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.soggetti
    ADD CONSTRAINT soggetti_codice_key UNIQUE (codice);


--
-- Name: soggetti soggetti_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.soggetti
    ADD CONSTRAINT soggetti_pkey PRIMARY KEY (id);


--
-- Name: utenti utenti_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_pkey PRIMARY KEY (id);


--
-- Name: utenti utenti_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_username_key UNIQUE (username);


--
-- Name: idx_aeroporti_iata; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_aeroporti_iata ON public.aeroporti USING btree (iata_code);


--
-- Name: idx_aeroporti_icao; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_aeroporti_icao ON public.aeroporti USING btree (icao_code);


--
-- Name: idx_aeroporti_lat; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_aeroporti_lat ON public.aeroporti USING btree (latitudine);


--
-- Name: idx_aeroporti_lon; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_aeroporti_lon ON public.aeroporti USING btree (longitudine);


--
-- Name: idx_aeroporti_nazione; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_aeroporti_nazione ON public.aeroporti USING btree (iso_nazione);


--
-- Name: idx_localita_citta_lower; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_citta_lower ON public.localita USING btree (lower((citta)::text));


--
-- Name: idx_localita_citta_trgm; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_citta_trgm ON public.localita USING gin (lower((citta)::text) public.gin_trgm_ops);


--
-- Name: idx_localita_identita; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_localita_identita ON public.localita USING btree (COALESCE(codice, ''::character varying), iso_nazione, nome, latitudine, longitudine);


--
-- Name: idx_localita_iso_lat_lon_attive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_iso_lat_lon_attive ON public.localita USING btree (iso_nazione, latitudine, longitudine) WHERE (attivo = true);


--
-- Name: idx_localita_iso_nazione; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_iso_nazione ON public.localita USING btree (iso_nazione);


--
-- Name: idx_localita_latitudine; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_latitudine ON public.localita USING btree (latitudine);


--
-- Name: idx_localita_longitudine; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_longitudine ON public.localita USING btree (longitudine);


--
-- Name: idx_localita_nazione; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_nazione ON public.localita USING btree (nazione);


--
-- Name: idx_localita_nome_lower; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_nome_lower ON public.localita USING btree (lower((nome)::text));


--
-- Name: idx_localita_nome_trgm; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_nome_trgm ON public.localita USING gin (lower((nome)::text) public.gin_trgm_ops);


--
-- Name: idx_localita_popolazione; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_localita_popolazione ON public.localita USING btree (popolazione DESC NULLS LAST);


--
-- Name: idx_sessioni_rl_soggetto; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sessioni_rl_soggetto ON public.sessioni_rl USING btree (soggetto_id, utente_id);


--
-- Name: idx_sessioni_rs_soggetto; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sessioni_rs_soggetto ON public.sessioni_rs USING btree (soggetto_id, utente_id);


--
-- Name: idx_soggetti_res_nazione; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_soggetti_res_nazione ON public.soggetti USING btree (residenza_nazione);


--
-- Name: idx_soggetti_utente; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_soggetti_utente ON public.soggetti USING btree (utente_id);


--
-- Name: idx_utenti_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_utenti_username ON public.utenti USING btree (username);


--
-- Name: log_calcoli log_calcoli_soggetto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_calcoli
    ADD CONSTRAINT log_calcoli_soggetto_id_fkey FOREIGN KEY (soggetto_id) REFERENCES public.soggetti(id) ON DELETE CASCADE;


--
-- Name: preferiti preferiti_soggetto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.preferiti
    ADD CONSTRAINT preferiti_soggetto_id_fkey FOREIGN KEY (soggetto_id) REFERENCES public.soggetti(id) ON DELETE CASCADE;


--
-- Name: preferiti preferiti_utente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.preferiti
    ADD CONSTRAINT preferiti_utente_id_fkey FOREIGN KEY (utente_id) REFERENCES public.utenti(id) ON DELETE SET NULL;


--
-- Name: sessioni_rl sessioni_rl_sessione_rs_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rl
    ADD CONSTRAINT sessioni_rl_sessione_rs_id_fkey FOREIGN KEY (sessione_rs_id) REFERENCES public.sessioni_rs(id) ON DELETE SET NULL;


--
-- Name: sessioni_rl sessioni_rl_soggetto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rl
    ADD CONSTRAINT sessioni_rl_soggetto_id_fkey FOREIGN KEY (soggetto_id) REFERENCES public.soggetti(id) ON DELETE CASCADE;


--
-- Name: sessioni_rl sessioni_rl_utente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rl
    ADD CONSTRAINT sessioni_rl_utente_id_fkey FOREIGN KEY (utente_id) REFERENCES public.utenti(id) ON DELETE SET NULL;


--
-- Name: sessioni_rs sessioni_rs_soggetto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rs
    ADD CONSTRAINT sessioni_rs_soggetto_id_fkey FOREIGN KEY (soggetto_id) REFERENCES public.soggetti(id) ON DELETE CASCADE;


--
-- Name: sessioni_rs sessioni_rs_utente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessioni_rs
    ADD CONSTRAINT sessioni_rs_utente_id_fkey FOREIGN KEY (utente_id) REFERENCES public.utenti(id) ON DELETE SET NULL;


--
-- Name: soggetti soggetti_utente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.soggetti
    ADD CONSTRAINT soggetti_utente_id_fkey FOREIGN KEY (utente_id) REFERENCES public.utenti(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict QuZv8BW24gQ9ZwnFYRVmkvPXSZxNZTGm7N07hDgxSeY1TdPXVa3RQoq5vazjYX6

