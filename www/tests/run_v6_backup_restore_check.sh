#!/usr/bin/env bash
set -euo pipefail

DB_CONTAINER="${V6_DB_CONTAINER:-astro-val-db}"
BACKUP_FILE="${V6_BACKUP_FILE:-/tmp/astro-val-v6-backup-test.dump}"
TEST_DB="${V6_RESTORE_DATABASE:-astro_val_v6_restore_test}"

cleanup() {
    docker exec "$DB_CONTAINER" sh -lc \
        "dropdb \
            --if-exists \
            --username=\"\$POSTGRES_USER\" \
            \"$TEST_DB\"" \
        >/dev/null 2>&1 || true

    rm -f "$BACKUP_FILE"
}

trap cleanup EXIT

printf '===== V6 BACKUP AND RESTORE CHECK =====\n'

rm -f "$BACKUP_FILE"

docker exec "$DB_CONTAINER" sh -lc \
    'pg_dump \
        --format=custom \
        --no-owner \
        --no-privileges \
        --dbname="$POSTGRES_DB" \
        --username="$POSTGRES_USER"' \
    > "$BACKUP_FILE"

if [[ ! -s "$BACKUP_FILE" ]]; then
    printf 'Backup PostgreSQL vuoto o mancante\n' >&2
    exit 1
fi

backup_size="$(stat -c %s "$BACKUP_FILE")"

if (( backup_size < 1024 )); then
    printf 'Backup PostgreSQL troppo piccolo: %s byte\n' \
        "$backup_size" >&2
    exit 1
fi

docker exec -i "$DB_CONTAINER" pg_restore --list \
    < "$BACKUP_FILE" \
    | grep -Fq 'TABLE public utenti'

docker exec -i "$DB_CONTAINER" pg_restore --list \
    < "$BACKUP_FILE" \
    | grep -Fq 'TABLE public soggetti'

docker exec "$DB_CONTAINER" sh -lc \
    "dropdb \
        --if-exists \
        --username=\"\$POSTGRES_USER\" \
        \"$TEST_DB\" && \
     createdb \
        --username=\"\$POSTGRES_USER\" \
        \"$TEST_DB\""

docker exec -i "$DB_CONTAINER" sh -lc \
    "pg_restore \
        --exit-on-error \
        --no-owner \
        --no-privileges \
        --username=\"\$POSTGRES_USER\" \
        --dbname=\"$TEST_DB\"" \
    < "$BACKUP_FILE"

tables="$(
    docker exec "$DB_CONTAINER" sh -lc \
        "psql \
            --username=\"\$POSTGRES_USER\" \
            --dbname=\"$TEST_DB\" \
            --tuples-only \
            --no-align \
            --command=\"
                SELECT tablename
                FROM pg_tables
                WHERE schemaname = 'public'
                ORDER BY tablename;
            \""
)"

for required_table in \
    aeroporti \
    log_calcoli \
    preferiti \
    sessioni_rl \
    sessioni_rs \
    soggetti \
    utenti; do
    if ! grep -Fxq "$required_table" <<< "$tables"; then
        printf 'Tabella ripristinata mancante: %s\n' \
            "$required_table" >&2
        exit 1
    fi
done

counts="$(
    docker exec "$DB_CONTAINER" sh -lc \
        "psql \
            --username=\"\$POSTGRES_USER\" \
            --dbname=\"$TEST_DB\" \
            --tuples-only \
            --no-align \
            --command=\"
                SELECT
                    (SELECT count(*) FROM utenti),
                    (SELECT count(*) FROM soggetti);
            \""
)"

IFS='|' read -r users_count subjects_count <<< "$counts"

if [[ ! "$users_count" =~ ^[0-9]+$ ]]; then
    printf 'Conteggio utenti non valido: %s\n' \
        "$users_count" >&2
    exit 1
fi

if [[ ! "$subjects_count" =~ ^[0-9]+$ ]]; then
    printf 'Conteggio soggetti non valido: %s\n' \
        "$subjects_count" >&2
    exit 1
fi

printf '\n===== V6 BACKUP METRICS =====\n'
printf 'backup_bytes      : %s\n' "$backup_size"
printf 'restored_tables   : 7\n'
printf 'restored_users    : %s\n' "$users_count"
printf 'restored_subjects : %s\n' "$subjects_count"

printf '\nV6 BACKUP AND RESTORE CHECK OK\n'
