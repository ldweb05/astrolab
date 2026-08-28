BEGIN;

ALTER TABLE utenti
    ADD COLUMN subjects_limit_override INTEGER,
    ADD COLUMN donazione_importo NUMERIC(10,2),
    ADD COLUMN supporter_inizio DATE,
    ADD COLUMN supporter_scadenza DATE,
    ADD COLUMN accesso_speciale_permanente BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN note_piano TEXT,
    ADD CONSTRAINT utenti_subjects_limit_override_check
        CHECK (subjects_limit_override IS NULL OR subjects_limit_override >= 0),
    ADD CONSTRAINT utenti_donazione_importo_check
        CHECK (donazione_importo IS NULL OR donazione_importo >= 0),
    ADD CONSTRAINT utenti_supporter_date_check
        CHECK (
            supporter_inizio IS NULL
            OR supporter_scadenza IS NULL
            OR supporter_scadenza >= supporter_inizio
        );

ALTER TABLE piani
    ADD COLUMN donazione_minima NUMERIC(10,2),
    ADD COLUMN durata_giorni INTEGER,
    ADD CONSTRAINT piani_donazione_minima_check
        CHECK (donazione_minima IS NULL OR donazione_minima >= 0),
    ADD CONSTRAINT piani_durata_giorni_check
        CHECK (durata_giorni IS NULL OR durata_giorni > 0);

UPDATE piani
SET donazione_minima = 0,
    durata_giorni = 365,
    updated_at = CURRENT_TIMESTAMP
WHERE code = 'supporter';

COMMIT;
