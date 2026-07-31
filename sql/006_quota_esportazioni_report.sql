BEGIN;

INSERT INTO piano_limiti (
    plan_id,
    feature_code,
    limit_value,
    enabled,
    period_type
)
SELECT
    id,
    'annual_report_exports_max',
    3,
    TRUE,
    'monthly'
FROM piani
WHERE code = 'free'
ON CONFLICT (plan_id, feature_code)
DO UPDATE SET
    limit_value = EXCLUDED.limit_value,
    enabled = EXCLUDED.enabled,
    period_type = EXCLUDED.period_type,
    updated_at = CURRENT_TIMESTAMP;

INSERT INTO piano_limiti (
    plan_id,
    feature_code,
    limit_value,
    enabled,
    period_type
)
SELECT
    id,
    'annual_report_exports_max',
    NULL,
    TRUE,
    'monthly'
FROM piani
WHERE code = 'supporter'
ON CONFLICT (plan_id, feature_code)
DO UPDATE SET
    limit_value = EXCLUDED.limit_value,
    enabled = EXCLUDED.enabled,
    period_type = EXCLUDED.period_type,
    updated_at = CURRENT_TIMESTAMP;

CREATE TABLE annual_report_export_usage (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT NOT NULL
                    REFERENCES utenti(id) ON DELETE CASCADE,
    subject_id      BIGINT
                    REFERENCES soggetti(id) ON DELETE SET NULL,
    feature_code    VARCHAR(100) NOT NULL
                    DEFAULT 'annual_report_exports_max',
    export_type     VARCHAR(30) NOT NULL,
    period_start    DATE NOT NULL,
    idempotency_key VARCHAR(100) NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT annual_report_export_usage_type_check
        CHECK (export_type IN ('browser_print', 'pdf')),
    CONSTRAINT annual_report_export_usage_unique_request
        UNIQUE (
            user_id,
            feature_code,
            period_start,
            idempotency_key
        )
);

CREATE INDEX idx_annual_report_export_usage_monthly_count
    ON annual_report_export_usage (
        user_id,
        feature_code,
        period_start
    );

COMMIT;
