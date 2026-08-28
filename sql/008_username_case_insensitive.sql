BEGIN;

CREATE UNIQUE INDEX idx_utenti_username_lower
    ON utenti (LOWER(TRIM(username)));

COMMIT;
