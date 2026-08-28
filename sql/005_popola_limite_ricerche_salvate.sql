BEGIN;

INSERT INTO piano_limiti (plan_id, feature_code, limit_value, enabled)
SELECT id, 'saved_searches_max', 10, TRUE
FROM piani
WHERE code = 'free'
ON CONFLICT (plan_id, feature_code)
DO UPDATE SET
    limit_value = EXCLUDED.limit_value,
    enabled = EXCLUDED.enabled,
    updated_at = CURRENT_TIMESTAMP;

INSERT INTO piano_limiti (plan_id, feature_code, limit_value, enabled)
SELECT id, 'saved_searches_max', NULL, TRUE
FROM piani
WHERE code = 'supporter'
ON CONFLICT (plan_id, feature_code)
DO UPDATE SET
    limit_value = EXCLUDED.limit_value,
    enabled = EXCLUDED.enabled,
    updated_at = CURRENT_TIMESTAMP;

COMMIT;
