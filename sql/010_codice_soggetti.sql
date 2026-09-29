-- 010_codice_soggetti.sql
-- Blocco A, Fase A1 - docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md (C1, C2)
-- Prefisso di 2 lettere dell'astrologo, usato per generare il CODICE dei soggetti
-- (es. LD001, RF120). Salvato una sola volta: non cambia se cambia il nome.

BEGIN;

ALTER TABLE utenti
    ADD COLUMN prefisso_codice VARCHAR(2),
    ADD CONSTRAINT utenti_prefisso_codice_key UNIQUE (prefisso_codice),
    ADD CONSTRAINT utenti_prefisso_codice_check
        CHECK (prefisso_codice IS NULL OR prefisso_codice ~ '^[A-Z]{2}$');

-- Prefissi degli utenti esistenti, coerenti con i codici soggetto gia' in uso (C2).
-- Ogni UPDATE deve trovare esattamente l'utente atteso, altrimenti la migrazione
-- viene annullata per intero.
DO $$
DECLARE
    n INTEGER;
BEGIN
    UPDATE utenti SET prefisso_codice = 'RF' WHERE LOWER(TRIM(username)) = 'roxy';
    GET DIAGNOSTICS n = ROW_COUNT;
    IF n <> 1 THEN RAISE EXCEPTION 'utente roxy: attese 1 riga, trovate %', n; END IF;

    UPDATE utenti SET prefisso_codice = 'LD' WHERE LOWER(TRIM(username)) = 'lodian';
    GET DIAGNOSTICS n = ROW_COUNT;
    IF n <> 1 THEN RAISE EXCEPTION 'utente lodian: attese 1 riga, trovate %', n; END IF;

    UPDATE utenti SET prefisso_codice = 'AD' WHERE LOWER(TRIM(username)) = 'admin';
    GET DIAGNOSTICS n = ROW_COUNT;
    IF n <> 1 THEN RAISE EXCEPTION 'utente admin: attese 1 riga, trovate %', n; END IF;
END $$;

COMMIT;
