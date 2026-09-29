-- 012_accessi_soggetti.sql
-- Blocco B, Fase B1 - docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md (C9-C12)
-- Credenziali dei soggetti, separate da quelle degli astrologi (tabella utenti).
-- Username del soggetto = soggetti.codice (non duplicato qui).

BEGIN;

SET search_path = public;

-- Una riga per ogni soggetto a cui l'astrologo ha abilitato l'accesso.
CREATE TABLE accessi_soggetti (
    soggetto_id             INTEGER PRIMARY KEY
                            REFERENCES soggetti(id) ON DELETE CASCADE,
    password_hash           VARCHAR(255) NOT NULL,
    attivo                  BOOLEAN NOT NULL DEFAULT TRUE,
    deve_cambiare_password  BOOLEAN NOT NULL DEFAULT TRUE,
    tentativi_falliti       INTEGER NOT NULL DEFAULT 0,
    bloccato_fino           TIMESTAMPTZ,
    ultimo_accesso          TIMESTAMPTZ,
    password_impostata_il   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    creato_il               TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    abilitato_da            INTEGER REFERENCES utenti(id) ON DELETE SET NULL,
    CONSTRAINT accessi_soggetti_tentativi_check CHECK (tentativi_falliti >= 0)
);

-- Tentativi di login falliti per indirizzo IP (C12): impedisce di provare la
-- stessa password su molti codici in sequenza (LD001, LD002, ...).
-- L'IP e' salvato solo come hash SHA-256, mai in chiaro.
CREATE TABLE tentativi_login_soggetti (
    id          BIGSERIAL PRIMARY KEY,
    ip_hash     CHAR(64) NOT NULL,
    creato_il   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tentativi_login_soggetti_ip
    ON tentativi_login_soggetti (ip_hash, creato_il);

COMMIT;
