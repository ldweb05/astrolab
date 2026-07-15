#!/usr/bin/env bash
set -euo pipefail

REPOSITORY_ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/.." &&
    pwd
)"

BASE_COMPOSE="$REPOSITORY_ROOT/docker-compose.yml"
PRODUCTION_COMPOSE="$REPOSITORY_ROOT/docker-compose.production.yml"
DEVELOPMENT_ENV="$REPOSITORY_ROOT/.env"
PRODUCTION_ENV="$REPOSITORY_ROOT/.env.production"

read_env_value() {
    local file="$1"
    local key="$2"

    awk -F= \
        -v requested_key="$key" \
        '
        /^[[:space:]]*#/ { next }
        /^[[:space:]]*$/ { next }

        {
            current_key=$1
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", current_key)

            if (current_key == requested_key) {
                value=substr($0, index($0, "=") + 1)
                gsub(/^[[:space:]]+|[[:space:]]+$/, "", value)
                print value
                exit
            }
        }
        ' "$file"
}

require_env_file() {
    local file="$1"

    if [[ ! -s "$file" ]]; then
        printf 'File ambiente mancante o vuoto: %s\n' "$file" >&2
        exit 1
    fi
}

validate_development_env() {
    require_env_file "$DEVELOPMENT_ENV"

    local app_env
    local app_debug

    app_env="$(read_env_value "$DEVELOPMENT_ENV" APP_ENV)"
    app_debug="$(read_env_value "$DEVELOPMENT_ENV" APP_DEBUG)"

    if [[ "$app_env" != "development" ]]; then
        printf '.env deve contenere APP_ENV=development\n' >&2
        exit 1
    fi

    if [[ "$app_debug" != "true" ]]; then
        printf '.env deve contenere APP_DEBUG=true\n' >&2
        exit 1
    fi
}

validate_production_env() {
    require_env_file "$PRODUCTION_ENV"

    local app_env
    local app_debug

    app_env="$(read_env_value "$PRODUCTION_ENV" APP_ENV)"
    app_debug="$(read_env_value "$PRODUCTION_ENV" APP_DEBUG)"

    if [[ "$app_env" != "production" ]]; then
        printf '.env.production deve contenere APP_ENV=production\n' >&2
        exit 1
    fi

    if [[ "$app_debug" != "false" ]]; then
        printf '.env.production deve contenere APP_DEBUG=false\n' >&2
        exit 1
    fi
}

show_status() {
    printf '===== ASTRO-VAL ENVIRONMENT STATUS =====\n'

    if ! docker inspect astro-val-web >/dev/null 2>&1; then
        printf 'container_web : NON DISPONIBILE\n'
        exit 0
    fi

    current_env="$(
        docker exec astro-val-web sh -lc \
            'printf "%s" "${APP_ENV:-MANCANTE}"'
    )"

    current_debug="$(
        docker exec astro-val-web sh -lc \
            'printf "%s" "${APP_DEBUG:-MANCANTE}"'
    )"

    printf 'container_web : ATTIVO\n'
    printf 'APP_ENV       : %s\n' "$current_env"
    printf 'APP_DEBUG     : %s\n' "$current_debug"

    if [[ "$current_env" == "production" && "$current_debug" == "false" ]]; then
        printf 'mode          : PRODUCTION\n'
    elif [[ "$current_env" == "development" && "$current_debug" == "true" ]]; then
        printf 'mode          : DEVELOPMENT\n'
    else
        printf 'mode          : CONFIGURAZIONE NON COERENTE\n'
        exit 1
    fi
}

start_development() {
    validate_development_env

    docker compose \
        -f "$BASE_COMPOSE" \
        up -d \
        --force-recreate

    show_status
}

start_production() {
    validate_production_env

    docker compose \
        -f "$BASE_COMPOSE" \
        -f "$PRODUCTION_COMPOSE" \
        up -d \
        --force-recreate

    show_status

    current_env="$(
        docker exec astro-val-web sh -lc \
            'printf "%s" "${APP_ENV:-}"'
    )"

    current_debug="$(
        docker exec astro-val-web sh -lc \
            'printf "%s" "${APP_DEBUG:-}"'
    )"

    if [[ "$current_env" != "production" || "$current_debug" != "false" ]]; then
        printf 'Avvio production non riuscito in modo sicuro\n' >&2
        exit 1
    fi
}

usage() {
    printf '%s\n' \
        'Uso:' \
        '  tools/environment.sh status' \
        '  tools/environment.sh development' \
        '  tools/environment.sh production'
}

command="${1:-status}"

case "$command" in
    status)
        show_status
        ;;
    development)
        start_development
        ;;
    production)
        start_production
        ;;
    *)
        usage >&2
        exit 1
        ;;
esac
