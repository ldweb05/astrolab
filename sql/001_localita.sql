BEGIN;

CREATE EXTENSION IF NOT EXISTS pg_trgm;

CREATE TABLE IF NOT EXISTS localita (
    id           BIGSERIAL PRIMARY KEY,
    codice       VARCHAR(40),
    nome         VARCHAR(200) NOT NULL,
    citta        VARCHAR(200) NOT NULL,
    nazione      VARCHAR(100) NOT NULL,
    iso_nazione  VARCHAR(10),
    latitudine   NUMERIC(9,6) NOT NULL,
    longitudine  NUMERIC(9,6) NOT NULL,
    popolazione  BIGINT,
    tipo         VARCHAR(50) NOT NULL DEFAULT 'localita',
    fonte        VARCHAR(50),
    attivo       BOOLEAN NOT NULL DEFAULT TRUE,
    creato_il    TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT localita_latitudine_check
        CHECK (latitudine BETWEEN -90 AND 90),
    CONSTRAINT localita_longitudine_check
        CHECK (longitudine BETWEEN -180 AND 180)
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_localita_identita
    ON localita (
        COALESCE(codice, ''),
        iso_nazione,
        nome,
        latitudine,
        longitudine
    );

CREATE INDEX IF NOT EXISTS idx_localita_iso_nazione
    ON localita (iso_nazione);

CREATE INDEX IF NOT EXISTS idx_localita_nazione
    ON localita (nazione);

CREATE INDEX IF NOT EXISTS idx_localita_latitudine
    ON localita (latitudine);

CREATE INDEX IF NOT EXISTS idx_localita_longitudine
    ON localita (longitudine);

CREATE INDEX IF NOT EXISTS idx_localita_nome_lower
    ON localita (LOWER(nome));

CREATE INDEX IF NOT EXISTS idx_localita_citta_lower
    ON localita (LOWER(citta));

CREATE INDEX IF NOT EXISTS idx_localita_popolazione
    ON localita (popolazione DESC NULLS LAST);

CREATE INDEX IF NOT EXISTS idx_localita_nome_trgm
    ON localita USING gin (LOWER(nome) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_localita_citta_trgm
    ON localita USING gin (LOWER(citta) gin_trgm_ops);

COMMIT;
