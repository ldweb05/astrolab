#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SUITE="$ROOT/tests/run_v6_hardening.sh"
MAX_SECONDS="${V6_RELEASE_MAX_SECONDS:-180}"

printf "===== V6 RELEASE CHECK =====\n"

start_seconds="$(date +%s)"

timeout "$MAX_SECONDS" "$SUITE"

end_seconds="$(date +%s)"
elapsed_seconds=$((end_seconds - start_seconds))

if (( elapsed_seconds > MAX_SECONDS )); then
    printf "V6 RELEASE CHECK oltre il budget: %ss\n" "$elapsed_seconds" >&2
    exit 1
fi

printf "\n===== V6 RELEASE METRICS =====\n"
printf "total_time_seconds : %d\n" "$elapsed_seconds"
printf "timeout_seconds    : %d\n" "$MAX_SECONDS"
printf "\nV6 RELEASE CHECK OK\n"
