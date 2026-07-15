#!/usr/bin/env bash
set -euo pipefail

REPOSITORY_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
APPLICATION_ROOT="$REPOSITORY_ROOT/www"
RELEASE_CHECK="$APPLICATION_ROOT/tests/run_v6_release_check.sh"
REPORT_FILE="/tmp/astro-val-v6-rc2-report.txt"

expected_branch="${V6_RELEASE_BRANCH:-feature/sintesi-rsm}"
current_branch="$(git -C "$REPOSITORY_ROOT" branch --show-current)"

cleanup() {
    rm -f "$REPORT_FILE.tmp"
}

trap cleanup EXIT

printf '===== V6 RC2 CHECK =====\n'

if [[ "$current_branch" != "$expected_branch" ]]; then
    printf 'Branch inatteso: %s, atteso %s\n' \
        "$current_branch" \
        "$expected_branch" >&2
    exit 1
fi

if [[ -n "$(git -C "$REPOSITORY_ROOT" status --porcelain)" ]]; then
    git -C "$REPOSITORY_ROOT" status --short
    printf 'Working Tree non pulito\n' >&2
    exit 1
fi

required_files=(
    "$REPOSITORY_ROOT/docker-compose.yml"
    "$REPOSITORY_ROOT/Dockerfile"
    "$REPOSITORY_ROOT/.env.example"
    "$REPOSITORY_ROOT/docs/START_HERE.md"
    "$REPOSITORY_ROOT/docs/ROADMAP.md"
    "$REPOSITORY_ROOT/docs/HANDOVER_OPERATIVO.md"
    "$APPLICATION_ROOT/composer.json"
    "$APPLICATION_ROOT/composer.lock"
    "$APPLICATION_ROOT/tests/run_v6_hardening.sh"
    "$APPLICATION_ROOT/tests/run_v6_release_check.sh"
    "$APPLICATION_ROOT/tests/run_v6_backup_restore_check.sh"
    "$APPLICATION_ROOT/tests/fixtures/rule_engine_freeze.json"
)

for required_file in "${required_files[@]}"; do
    if [[ ! -s "$required_file" ]]; then
        printf 'File release mancante o vuoto: %s\n' \
            "$required_file" >&2
        exit 1
    fi
done

if [[ -n "$(
    git -C "$REPOSITORY_ROOT" ls-files \
        '.env' \
        '.env.bak*' \
        '*.dump'
)" ]]; then
    printf 'File sensibili tracciati da Git\n' >&2
    exit 1
fi

if [[ -n "$(
    git -C "$REPOSITORY_ROOT" ls-files \
        | grep -Ei '(^|/)(backup|bkup).*\.sql$' \
        || true
)" ]]; then
    printf 'Backup SQL sensibili tracciati da Git\n' >&2
    exit 1
fi

grep -Fq \
    'V6 — Hardening e Release 1.0' \
    "$REPOSITORY_ROOT/docs/ROADMAP.md"

grep -Fq \
    'V6 Hardening e Release Check' \
    "$REPOSITORY_ROOT/docs/HANDOVER_OPERATIVO.md"

release_output="$(
    docker exec \
        -e ASTRO_VAL_BASE_URL=http://127.0.0.1 \
        astro-val-web sh -lc \
        '/var/www/html/tests/run_v6_release_check.sh'
)"

printf '%s\n' "$release_output"

if ! grep -Fq \
    'V6 RELEASE CHECK OK' \
    <<< "$release_output"; then
    printf 'Release Check V6 non superato\n' >&2
    exit 1
fi

backup_restore_output="$(
    "$APPLICATION_ROOT/tests/run_v6_backup_restore_check.sh"
)"

printf '%s\n' "$backup_restore_output"

if ! grep -Fq \
    'V6 BACKUP AND RESTORE CHECK OK' \
    <<< "$backup_restore_output"; then
    printf 'Backup e ripristino PostgreSQL non validi\n' >&2
    exit 1
fi

rule_freeze_output="$(
    docker exec astro-val-web php \
        -d error_reporting=E_ALL \
        -d display_errors=1 \
        /var/www/html/tests/test_rule_engine_freeze.php
)"

if ! grep -Fq \
    'RULE ENGINE FREEZE OK' \
    <<< "$rule_freeze_output"; then
    printf 'Rule Engine FREEZE non valido\n' >&2
    exit 1
fi

last_commit="$(
    git -C "$REPOSITORY_ROOT" log \
        -1 \
        --format='%H%n%h%n%s%n%cI'
)"

{
    printf 'ASTRO-VAL V6 RC2 REPORT\n'
    printf '=======================\n'
    printf 'branch: %s\n' "$current_branch"
    printf 'generated_at_utc: %s\n' "$(date -u +%FT%TZ)"
    printf 'working_tree: CLEAN\n'
    printf 'rule_engine: 120 RULE — FREEZE OK\n'
    printf 'release_check: OK\n'
    printf 'backup_restore: OK\n'
    printf '\nLAST COMMIT\n'
    printf '%s\n' "$last_commit"
    printf '\nRELEASE OUTPUT\n'
    printf '%s\n' "$release_output"
    printf '\nBACKUP RESTORE OUTPUT\n'
    printf '%s\n' "$backup_restore_output"
} > "$REPORT_FILE.tmp"

mv "$REPORT_FILE.tmp" "$REPORT_FILE"

if [[ ! -s "$REPORT_FILE" ]]; then
    printf 'Report RC2 non generato\n' >&2
    exit 1
fi

if [[ -n "$(git -C "$REPOSITORY_ROOT" status --porcelain)" ]]; then
    printf 'Repository modificato durante RC2 Check\n' >&2
    exit 1
fi

printf '\n===== V6 RC2 RESULT =====\n'
printf 'branch             : %s\n' "$current_branch"
printf 'working_tree       : CLEAN\n'
printf 'rule_engine        : FREEZE OK\n'
printf 'release_check      : OK\n'
printf 'backup_restore     : OK\n'
printf 'report             : %s\n' "$REPORT_FILE"
printf '\nV6 RC2 CHECK OK\n'
