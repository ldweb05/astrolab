BEGIN;
SET search_path = public;
SET search_path = public;

CREATE TABLE piani (
    id          BIGSERIAL PRIMARY KEY,
    code        VARCHAR(50) NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE piano_limiti (
    id           BIGSERIAL PRIMARY KEY,
    plan_id      BIGINT NOT NULL REFERENCES piani(id) ON DELETE CASCADE,
    feature_code VARCHAR(100) NOT NULL,
    limit_value  INTEGER,
    enabled      BOOLEAN NOT NULL DEFAULT TRUE,
    period_type  VARCHAR(30),
    created_at   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT piano_limiti_plan_feature_key UNIQUE (plan_id, feature_code)
);
ALTER TABLE utenti
    DROP CONSTRAINT utenti_ruolo_check;

UPDATE utenti
SET ruolo = 'user'
WHERE ruolo = 'astrologo';

ALTER TABLE utenti
    ADD COLUMN account_status VARCHAR(30) NOT NULL DEFAULT 'active',
    ADD COLUMN email_verified_at TIMESTAMPTZ,
    ADD COLUMN plan_id BIGINT,
    ADD COLUMN is_beta_tester BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN suspended_at TIMESTAMPTZ,
    ADD COLUMN suspension_reason TEXT,
    ADD CONSTRAINT utenti_ruolo_check
        CHECK (ruolo IN ('admin', 'user')),
    ADD CONSTRAINT utenti_account_status_check
        CHECK (account_status IN ('pending_email', 'active', 'suspended')),
    ADD CONSTRAINT utenti_plan_id_fkey
        FOREIGN KEY (plan_id) REFERENCES piani(id);

CREATE UNIQUE INDEX idx_utenti_email_lower
    ON utenti (LOWER(TRIM(email)));

INSERT INTO piani (code, name, description)
VALUES
    ('free', 'Free', 'Piano gratuito'),
    ('supporter', 'Supporter', 'Piano sostenitore');

UPDATE utenti
SET account_status = CASE
        WHEN attivo THEN 'active'
        ELSE 'suspended'
    END,
    email_verified_at = CASE
        WHEN attivo THEN COALESCE(ultimo_accesso, created_at)
        ELSE NULL
    END,
    plan_id = (
        SELECT id
        FROM piani
        WHERE code = 'supporter'
    ),
    updated_at = CURRENT_TIMESTAMP;

ALTER TABLE utenti
    ALTER COLUMN plan_id SET NOT NULL,
    ALTER COLUMN ruolo SET DEFAULT 'user';

COMMIT;
