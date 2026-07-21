#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONTAINER="${DB_CONTAINER:-astrolab-db}"

docker inspect "$CONTAINER" >/dev/null 2>&1 || {
    echo "Container PostgreSQL non trovato: $CONTAINER" >&2
    exit 1
}

printf '\n===== PREPARAZIONE DATABASE =====\n'

docker exec "$CONTAINER" sh -lc '
set -eu

: "${POSTGRES_USER:?POSTGRES_USER non impostata}"
: "${POSTGRES_DB:?POSTGRES_DB non impostata}"

psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --set ON_ERROR_STOP=1 <<SQL
CREATE EXTENSION IF NOT EXISTS pg_trgm;

DROP TABLE IF EXISTS localita_geonames_import;

CREATE UNLOGGED TABLE localita_geonames_import (
    codice       VARCHAR(40),
    nome         VARCHAR(200),
    citta        VARCHAR(200),
    nazione      VARCHAR(100),
    iso_nazione  VARCHAR(10),
    latitudine   NUMERIC(9,6),
    longitudine  NUMERIC(9,6),
    popolazione  BIGINT,
    tipo         VARCHAR(50),
    fonte        VARCHAR(50)
);
SQL
'

printf '\n===== CARICAMENTO STREAMING =====\n'

python3 "$ROOT/import/convert_geonames.py" |
docker exec -i "$CONTAINER" sh -lc '
set -eu

psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --set ON_ERROR_STOP=1 \
  --command "\copy localita_geonames_import FROM STDIN WITH (FORMAT csv, HEADER true, ENCODING '\''UTF8'\'')"
'

printf '\n===== CONSOLIDAMENTO =====\n'

docker exec "$CONTAINER" sh -lc '
set -eu

psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --set ON_ERROR_STOP=1 <<SQL
BEGIN;

DELETE FROM localita
WHERE fonte = '\''geonames'\'';

INSERT INTO localita (
    codice,
    nome,
    citta,
    nazione,
    iso_nazione,
    latitudine,
    longitudine,
    popolazione,
    tipo,
    fonte,
    attivo
)
SELECT
    codice,
    nome,
    citta,
    nazione,
    iso_nazione,
    latitudine,
    longitudine,
    popolazione,
    tipo,
    fonte,
    TRUE
FROM localita_geonames_import
WHERE nome <> '\''
  AND citta <> '\''
  AND nazione <> '\''
  AND latitudine BETWEEN -90 AND 90
  AND longitudine BETWEEN -180 AND 180
ON CONFLICT DO NOTHING;

COMMIT;

DROP TABLE localita_geonames_import;

CREATE INDEX IF NOT EXISTS idx_localita_nome_trgm
    ON localita USING gin (LOWER(nome) gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_localita_citta_trgm
    ON localita USING gin (LOWER(citta) gin_trgm_ops);

ANALYZE localita;

SELECT
    COUNT(*) AS totale_geonames,
    COUNT(DISTINCT iso_nazione) AS nazioni,
    COUNT(*) FILTER (WHERE popolazione > 0) AS con_popolazione,
    COUNT(*) FILTER (WHERE popolazione = 0) AS senza_popolazione
FROM localita
WHERE fonte = '\''geonames'\'';

SELECT
    tipo,
    COUNT(*) AS totale
FROM localita
WHERE fonte = '\''geonames'\''
GROUP BY tipo
ORDER BY totale DESC
LIMIT 20;
SQL
'

printf '\n===== DIMENSIONE DATABASE =====\n'

docker exec "$CONTAINER" sh -lc '
psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --command "
    SELECT
      pg_size_pretty(pg_total_relation_size('\''localita'\'')) AS dimensione_localita;
  "
'

printf '\nImportazione GeoNames completata.\n'
