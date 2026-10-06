-- 016_registro_accessi.sql
-- Registro accessi degli astrologi (06-10-2026), consultabile dall'admin in
-- admin_accessi.php. Una riga per ogni tentativo di login di un astrologo:
-- riuscito, fallito o bloccato. I login dei soggetti con il CODICE non sono
-- registrati (decisione del committente).
-- L'IP e' salvato solo come hash SHA-256, mai in chiaro (stessa regola di
-- tentativi_login). Le righe piu' vecchie di 6 mesi sono eliminate in automatico
-- dall'applicazione; l'admin puo' cancellarle in qualunque momento.

BEGIN;

SET search_path = public;

CREATE TABLE registro_accessi (
    id          BIGSERIAL PRIMARY KEY,
    -- NULL se lo username digitato non corrisponde a nessun utente;
    -- eliminando l'utente si eliminano anche le sue righe (dati personali)
    utente_id   INTEGER REFERENCES utenti(id) ON DELETE CASCADE,
    -- username digitato nel form (stessa lunghezza massima di utenti.username)
    username    VARCHAR(60) NOT NULL,
    esito       VARCHAR(10) NOT NULL,
    -- dettaglio dell'esito: credenziali, email_non_verificata, account_non_attivo,
    -- account_bloccato, ip_bloccato; NULL per gli accessi riusciti
    motivo      VARCHAR(30),
    ip_hash     CHAR(64) NOT NULL,
    user_agent  VARCHAR(255),
    creato_il   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT registro_accessi_esito_check
        CHECK (esito IN ('riuscito', 'fallito', 'bloccato')),
    CONSTRAINT registro_accessi_motivo_check
        CHECK (motivo IS NULL OR motivo IN ('credenziali', 'email_non_verificata',
               'account_non_attivo', 'account_bloccato', 'ip_bloccato'))
);

CREATE INDEX idx_registro_accessi_creato ON registro_accessi (creato_il);
CREATE INDEX idx_registro_accessi_utente ON registro_accessi (utente_id, creato_il);

COMMIT;
