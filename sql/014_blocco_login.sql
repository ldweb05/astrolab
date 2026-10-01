-- 014_blocco_login.sql
-- Correzione del PUNTO APERTO "Limite per IP del login" (docs/roadmaps/ROADMAP.md).
-- 1) blocco per account anche per gli astrologi: 5 password errate -> 15 minuti;
-- 2) tentativi falliti per IP nel DB (non piu' in file temporanei che si azzerano
--    al riavvio): 10 tentativi falliti in 15 minuti. L'IP e' salvato solo come hash.

BEGIN;

SET search_path = public;

ALTER TABLE utenti
    ADD COLUMN tentativi_falliti INTEGER NOT NULL DEFAULT 0,
    ADD COLUMN bloccato_fino TIMESTAMPTZ,
    ADD CONSTRAINT utenti_tentativi_falliti_check CHECK (tentativi_falliti >= 0);

CREATE TABLE tentativi_login (
    id          BIGSERIAL PRIMARY KEY,
    ip_hash     CHAR(64) NOT NULL,
    creato_il   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tentativi_login_ip ON tentativi_login (ip_hash, creato_il);

COMMIT;
