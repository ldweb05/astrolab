BEGIN;

SET search_path = public;

CREATE TABLE token_sicurezza (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT NOT NULL REFERENCES utenti(id) ON DELETE CASCADE,
    purpose     VARCHAR(30) NOT NULL,
    token_hash  CHAR(64) NOT NULL UNIQUE,
    expires_at  TIMESTAMPTZ NOT NULL,
    used_at     TIMESTAMPTZ,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    requested_ip VARCHAR(64),
    CONSTRAINT token_sicurezza_purpose_check
        CHECK (purpose IN ('email_verification', 'password_reset')),
    CONSTRAINT token_sicurezza_expiry_check
        CHECK (expires_at > created_at)
);

CREATE INDEX idx_token_sicurezza_user_purpose
    ON token_sicurezza (user_id, purpose);

CREATE INDEX idx_token_sicurezza_active
    ON token_sicurezza (purpose, expires_at)
    WHERE used_at IS NULL;

COMMIT;
