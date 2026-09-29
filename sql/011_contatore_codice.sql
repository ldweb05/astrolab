-- 011_contatore_codice.sql
-- Blocco A, Fase A2 - docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md (C6)
-- Contatore per astrologo dell'ultimo numero di CODICE emesso. Non torna mai
-- indietro: il numero di un soggetto eliminato non viene riassegnato a un altro.

BEGIN;

ALTER TABLE utenti
    ADD COLUMN ultimo_numero_codice INTEGER NOT NULL DEFAULT 0,
    ADD CONSTRAINT utenti_ultimo_numero_codice_check
        CHECK (ultimo_numero_codice >= 0);

-- Valore iniziale: numero piu' alto gia' usato con il prefisso dell'astrologo
-- (es. RF005 -> 5). Codici fuori formato (es. TEST01) non vengono considerati.
UPDATE utenti u
SET ultimo_numero_codice = COALESCE((
        SELECT MAX(CAST(SUBSTRING(s.codice FROM 3) AS INTEGER))
        FROM soggetti s
        WHERE s.codice ~ ('^' || u.prefisso_codice || '[0-9]+$')
    ), 0)
WHERE u.prefisso_codice IS NOT NULL;

COMMIT;
