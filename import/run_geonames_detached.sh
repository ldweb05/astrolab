#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONTAINER="${DB_CONTAINER:-astrolab-db}"

echo "===== AVVIO: $(date -Is) ====="

docker inspect "$CONTAINER" >/dev/null

echo "===== PREPARAZIONE DATABASE ====="
docker exec -i "$CONTAINER" sh -lc '
exec psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB"
' <<'SQL'
\set ON_ERROR_STOP on

DROP TABLE IF EXISTS localita_geonames_import;

TRUNCATE TABLE localita RESTART IDENTITY;

DROP INDEX IF EXISTS idx_localita_identita;
DROP INDEX IF EXISTS idx_localita_iso_nazione;
DROP INDEX IF EXISTS idx_localita_nazione;
DROP INDEX IF EXISTS idx_localita_latitudine;
DROP INDEX IF EXISTS idx_localita_longitudine;
DROP INDEX IF EXISTS idx_localita_nome_lower;
DROP INDEX IF EXISTS idx_localita_citta_lower;
DROP INDEX IF EXISTS idx_localita_popolazione;
DROP INDEX IF EXISTS idx_localita_nome_trgm;
DROP INDEX IF EXISTS idx_localita_citta_trgm;
SQL

echo "===== COPY GEONAMES: $(date -Is) ====="
python3 "$ROOT/import/convert_geonames.py" |
docker exec -i "$CONTAINER" sh -lc '
exec psql \
  --username "$POSTGRES_USER" \
  --dbname "$POSTGRES_DB" \
  --set ON_ERROR_STOP=1 \
  --command "\copy localita (codice,nome,citta,nazione,iso_nazione,latitudine,longitudine,popolazione,tipo,fonte) FROM STDIN WITH (FORMAT csv, HEADER true, ENCODING '\''UTF8'\'')"
'

echo "===== CREAZIONE INDICI: $(date -Is) ====="
docker exec -i "$CONTAINER" sh -lc '
exec psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB"
' <<'SQL'
\set ON_ERROR_STOP on

CREATE EXTENSION IF NOT EXISTS pg_trgm;

CREATE UNIQUE INDEX idx_localita_identita
    ON localita (
        COALESCE(codice, ''),
        iso_nazione,
        nome,
        latitudine,
        longitudine
    );

CREATE INDEX idx_localita_iso_nazione
    ON localita (iso_nazione);

CREATE INDEX idx_localita_nazione
    ON localita (nazione);

CREATE INDEX idx_localita_latitudine
    ON localita (latitudine);

CREATE INDEX idx_localita_longitudine
    ON localita (longitudine);

CREATE INDEX idx_localita_iso_lat_lon_attive
    ON localita (iso_nazione, latitudine, longitudine)
    WHERE attivo = true;

CREATE INDEX idx_localita_nome_lower
    ON localita (LOWER(nome));

CREATE INDEX idx_localita_citta_lower
    ON localita (LOWER(citta));

CREATE INDEX idx_localita_popolazione
    ON localita (popolazione DESC NULLS LAST);

CREATE INDEX idx_localita_nome_trgm
    ON localita USING gin (LOWER(nome) gin_trgm_ops);

CREATE INDEX idx_localita_citta_trgm
    ON localita USING gin (LOWER(citta) gin_trgm_ops);

ANALYZE localita;

SELECT
    COUNT(*) AS totale,
    COUNT(*) FILTER (WHERE fonte = 'geonames') AS geonames,
    COUNT(DISTINCT iso_nazione) AS nazioni,
    pg_size_pretty(pg_total_relation_size('localita')) AS dimensione
FROM localita;
SQL

echo "===== COMPLETATO: $(date -Is) ====="
