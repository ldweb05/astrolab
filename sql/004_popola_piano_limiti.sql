BEGIN;

INSERT INTO piano_limiti (plan_id, feature_code, limit_value, enabled)
SELECT id, 'subjects_max', 2, TRUE
FROM piani
WHERE code = 'free';

INSERT INTO piano_limiti (plan_id, feature_code, limit_value, enabled)
SELECT id, 'subjects_max', NULL, TRUE
FROM piani
WHERE code = 'supporter';

COMMIT;
