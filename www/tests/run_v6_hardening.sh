#!/usr/bin/env bash
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TESTS="$ROOT/tests"

run_test() {
    local test_file="$1"
    printf '\n===== %s =====\n' "$(basename "$test_file")"
    "$PHP_BIN" "$test_file"
}

run_test "$TESTS/test_annual_summary_builder.php"
run_test "$TESTS/test_executive_summary_narrative.php"
run_test "$TESTS/test_theme_summary_narrative.php"
run_test "$TESTS/test_cross_dynamics_builder.php"
run_test "$TESTS/test_conclusion_narrative_builder.php"
run_test "$TESTS/test_final_narrative_deduplication.php"
run_test "$TESTS/test_narrative_style_engine.php"
run_test "$TESTS/test_narrative_quality_duplicates.php"
run_test "$TESTS/test_annual_report_executive_summary.php"
run_test "$TESTS/test_annual_report_print_sanitizer.php"
run_test "$TESTS/test_annual_report_print_renderer.php"
run_test "$TESTS/test_annual_report_browser_print.php"
run_test "$TESTS/test_print_report_css.php"
run_test "$TESTS/test_annual_report_dompdf_smoke.php"
run_test "$TESTS/test_annual_report_real_cases.php"
run_test "$TESTS/test_annual_report.php"
run_test "$TESTS/test_regression_v3.php"

printf '\nV6 HARDENING SUITE OK\n'
