# 📁 Struttura del progetto Astrolab

> Aggiornata: 27/07/2026 11:37

```text
./
├── docs/
│   ├── status/
│   │   ├── KNOWLEDGE_COVERAGE.md
│   │   └── RULE_BACKLOG.md
│   ├── 01_PROJECT_MANIFESTO.md
│   ├── 02_ASTROLOGY.md
│   ├── 03_ARCHITECTURE_ASTROLAB.md
│   ├── 05_NARRATIVE.md
│   ├── 10_THEME_ENGINE.md
│   ├── 11_ANNUAL_REPORT_SPEC.md
│   ├── ADR_INDEX_ASTROLAB.md
│   ├── HANDOVER_OPERATIVO_astrolab.md
│   ├── PROJECT_RESUME_GUIDE.md
│   ├── README_ASTROLAB.md
│   ├── ROADMAP.md
│   ├── START_HERE.md
│   └── START_HERE.md.pre-final-docs
├── import/
│   ├── data/
│   │   ├── allCountries.zip
│   │   ├── cities1000.txt
│   │   ├── cities1000.zip
│   │   ├── countryInfo.txt
│   │   ├── geonames-import.log
│   │   ├── geonames-import.pid
│   │   └── localita_geonames.csv
│   ├── convert_geonames.py*
│   ├── import_geonames.sh*
│   └── run_geonames_detached.sh*
├── php-config/
│   └── php.ini
├── sql/
│   └── 001_localita.sql
├── tools/
│   ├── environment.sh*
│   ├── generate_doc.js
│   ├── migra_rsm_tipo_punto.py
│   ├── package.json
│   ├── package-lock.json
│   └── progetto_astrologia_overview.docx
├── www/
│   ├── api/
│   │   ├── cuspidi_search_api.php
│   │   ├── luoghi_api.php
│   │   ├── nazioni_localita_api.php
│   │   ├── ricerca_api.php*
│   │   ├── ricerca_griglia_api.php*
│   │   ├── ricerca_stream_api.php
│   │   ├── riloc_angolari_api.php
│   │   ├── rl_api.php
│   │   ├── rs_alert_api.php
│   │   ├── rs_api.php
│   │   ├── sensibilita_api.php
│   │   ├── session_api.php
│   │   ├── sessioni_api.php
│   │   ├── soggetti_api.php
│   │   ├── stampa_pdf_api.php
│   │   └── tema_api.php
│   ├── config/
│   │   └── previsione-annuale/
│   │       └── config.json
│   ├── css/
│   │   ├── print.css
│   │   └── style.css
│   ├── includes/
│   │   ├── atlas/
│   │   │   ├── AtlasLoader.php
│   │   │   ├── JupiterAtlas.php
│   │   │   ├── MarsAtlas.php
│   │   │   ├── MercuryAtlas.php
│   │   │   ├── MoonAtlas.php
│   │   │   ├── NeptuneAtlas.php
│   │   │   ├── PlutoAtlas.php
│   │   │   ├── RsmAtlas.php
│   │   │   ├── SaturnAtlas.php
│   │   │   ├── SunAtlas.php
│   │   │   ├── ThemeCatalog.php
│   │   │   ├── UranusAtlas.php
│   │   │   └── VenusAtlas.php
│   │   ├── forecast/
│   │   │   ├── rules/
│   │   │   │   ├── Rule0001_Jupiter10.php
│   │   │   │   ├── Rule0002_Saturn12.php
│   │   │   │   ├── Rule0003_Mars6.php
│   │   │   │   ├── Rule0004_Venus5.php
│   │   │   │   ├── Rule0005_Mercury3.php
│   │   │   │   ├── Rule0006_Moon4.php
│   │   │   │   ├── Rule0007_Uranus11.php
│   │   │   │   ├── Rule0008_Neptune9.php
│   │   │   │   ├── Rule0009_Pluto8.php
│   │   │   │   ├── Rule0010_Sun10.php
│   │   │   │   ├── Rule0011_Sun1.php
│   │   │   │   ├── Rule0012_Sun2.php
│   │   │   │   ├── Rule0013_Sun3.php
│   │   │   │   ├── Rule0014_Sun4.php
│   │   │   │   ├── Rule0015_Sun5.php
│   │   │   │   ├── Rule0016_Sun6.php
│   │   │   │   ├── Rule0017_Sun7.php
│   │   │   │   ├── Rule0018_Sun8.php
│   │   │   │   ├── Rule0019_Sun9.php
│   │   │   │   ├── Rule0020_Sun11.php
│   │   │   │   ├── Rule0021_Sun12.php
│   │   │   │   ├── Rule0022_Moon1.php
│   │   │   │   ├── Rule0023_Moon2.php
│   │   │   │   ├── Rule0024_Moon3.php
│   │   │   │   ├── Rule0025_Moon5.php
│   │   │   │   ├── Rule0026_Moon6.php
│   │   │   │   ├── Rule0027_Moon7.php
│   │   │   │   ├── Rule0028_Moon8.php
│   │   │   │   ├── Rule0029_Moon9.php
│   │   │   │   ├── Rule0030_Moon10.php
│   │   │   │   ├── Rule0031_Moon11.php
│   │   │   │   ├── Rule0032_Moon12.php
│   │   │   │   ├── Rule0033_Mercury1.php
│   │   │   │   ├── Rule0034_Mercury2.php
│   │   │   │   ├── Rule0035_Mercury4.php
│   │   │   │   ├── Rule0036_Mercury5.php
│   │   │   │   ├── Rule0037_Mercury6.php
│   │   │   │   ├── Rule0038_Mercury7.php
│   │   │   │   ├── Rule0039_Mercury8.php
│   │   │   │   ├── Rule0040_Mercury9.php
│   │   │   │   ├── Rule0041_Mercury10.php
│   │   │   │   ├── Rule0042_Mercury11.php
│   │   │   │   ├── Rule0043_Mercury12.php
│   │   │   │   ├── Rule0044_Venus1.php
│   │   │   │   ├── Rule0045_Venus2.php
│   │   │   │   ├── Rule0046_Venus3.php
│   │   │   │   ├── Rule0047_Venus4.php
│   │   │   │   ├── Rule0048_Venus6.php
│   │   │   │   ├── Rule0049_Venus7.php
│   │   │   │   ├── Rule0050_Venus8.php
│   │   │   │   ├── Rule0051_Venus9.php
│   │   │   │   ├── Rule0052_Venus10.php
│   │   │   │   ├── Rule0053_Venus11.php
│   │   │   │   ├── Rule0054_Venus12.php
│   │   │   │   ├── Rule0055_Mars1.php
│   │   │   │   ├── Rule0056_Mars2.php
│   │   │   │   ├── Rule0057_Mars3.php
│   │   │   │   ├── Rule0058_Mars4.php
│   │   │   │   ├── Rule0059_Mars5.php
│   │   │   │   ├── Rule0060_Mars7.php
│   │   │   │   ├── Rule0061_Mars8.php
│   │   │   │   ├── Rule0062_Mars9.php
│   │   │   │   ├── Rule0063_Mars10.php
│   │   │   │   ├── Rule0064_Mars11.php
│   │   │   │   ├── Rule0065_Mars12.php
│   │   │   │   ├── Rule0066_Jupiter1.php
│   │   │   │   ├── Rule0067_Jupiter2.php
│   │   │   │   ├── Rule0068_Jupiter3.php
│   │   │   │   ├── Rule0069_Jupiter4.php
│   │   │   │   ├── Rule0070_Jupiter5.php
│   │   │   │   ├── Rule0071_Jupiter6.php
│   │   │   │   ├── Rule0072_Jupiter7.php
│   │   │   │   ├── Rule0073_Jupiter8.php
│   │   │   │   ├── Rule0074_Jupiter9.php
│   │   │   │   ├── Rule0075_Jupiter11.php
│   │   │   │   ├── Rule0076_Jupiter12.php
│   │   │   │   ├── Rule0077_Saturn1.php
│   │   │   │   ├── Rule0078_Saturn2.php
│   │   │   │   ├── Rule0079_Saturn3.php
│   │   │   │   ├── Rule0080_Saturn4.php
│   │   │   │   ├── Rule0081_Saturn5.php
│   │   │   │   ├── Rule0082_Saturn6.php
│   │   │   │   ├── Rule0083_Saturn7.php
│   │   │   │   ├── Rule0084_Saturn8.php
│   │   │   │   ├── Rule0085_Saturn9.php
│   │   │   │   ├── Rule0086_Saturn10.php
│   │   │   │   ├── Rule0087_Saturn11.php
│   │   │   │   ├── Rule0088_Uranus1.php
│   │   │   │   ├── Rule0089_Uranus2.php
│   │   │   │   ├── Rule0090_Uranus3.php
│   │   │   │   ├── Rule0091_Uranus4.php
│   │   │   │   ├── Rule0092_Uranus5.php
│   │   │   │   ├── Rule0093_Uranus6.php
│   │   │   │   ├── Rule0094_Uranus7.php
│   │   │   │   ├── Rule0095_Uranus8.php
│   │   │   │   ├── Rule0096_Uranus9.php
│   │   │   │   ├── Rule0097_Uranus10.php
│   │   │   │   ├── Rule0098_Uranus12.php
│   │   │   │   ├── Rule0099_Neptune1.php
│   │   │   │   ├── Rule0100_Neptune2.php
│   │   │   │   ├── Rule0101_Neptune3.php
│   │   │   │   ├── Rule0102_Neptune4.php
│   │   │   │   ├── Rule0103_Neptune5.php
│   │   │   │   ├── Rule0104_Neptune6.php
│   │   │   │   ├── Rule0105_Neptune7.php
│   │   │   │   ├── Rule0106_Neptune8.php
│   │   │   │   ├── Rule0107_Neptune10.php
│   │   │   │   ├── Rule0108_Neptune11.php
│   │   │   │   ├── Rule0109_Neptune12.php
│   │   │   │   ├── Rule0110_Pluto1.php
│   │   │   │   ├── Rule0111_Pluto2.php
│   │   │   │   ├── Rule0112_Pluto3.php
│   │   │   │   ├── Rule0113_Pluto4.php
│   │   │   │   ├── Rule0114_Pluto5.php
│   │   │   │   ├── Rule0115_Pluto6.php
│   │   │   │   ├── Rule0116_Pluto7.php
│   │   │   │   ├── Rule0117_Pluto9.php
│   │   │   │   ├── Rule0118_Pluto10.php
│   │   │   │   ├── Rule0119_Pluto11.php
│   │   │   │   └── Rule0120_Pluto12.php
│   │   │   ├── AARuleEngine.php
│   │   │   ├── AdvancedContextEngine.php
│   │   │   ├── AdvancedForecastEngine.php
│   │   │   ├── AdvancedThemeAggregator.php
│   │   │   ├── AngularPowerEngine.php
│   │   │   ├── AnnualMeaningBuilder.php
│   │   │   ├── AnnualReportBuilder.php
│   │   │   ├── AnnualReportDraftBuilder.php
│   │   │   ├── AnnualReportOutlineBuilder.php
│   │   │   ├── AnnualReportPrintRenderer.php
│   │   │   ├── AnnualReportPrintSanitizer.php
│   │   │   ├── AnnualSummaryBuilder.php
│   │   │   ├── AspectEngine.php
│   │   │   ├── AspectInterpretationEngine.php
│   │   │   ├── AspectScoreEngine.php
│   │   │   ├── AstrologyRuleInterface.php
│   │   │   ├── AttentionNarrativeBuilder.php
│   │   │   ├── CompositeEvidenceEngine.php
│   │   │   ├── ConclusionNarrativeBuilder.php
│   │   │   ├── ContributionNormalizer.php
│   │   │   ├── CrossDynamicsBuilder.php
│   │   │   ├── DignityEngine.php
│   │   │   ├── DignityIntegrationEngine.php
│   │   │   ├── DominantThemeEngine.php
│   │   │   ├── EvidenceBuilder.php
│   │   │   ├── EvidenceEngine.php
│   │   │   ├── EvidenceFormatter.php
│   │   │   ├── ExecutiveSummaryNarrativeBuilder.php
│   │   │   ├── FinalNarrativeEngine.php
│   │   │   ├── ForecastContextEngine.php
│   │   │   ├── ForecastEngineV2.php
│   │   │   ├── ForecastEngineV3.php
│   │   │   ├── HouseDominanceEngine.php
│   │   │   ├── NarrativeComposer.php
│   │   │   ├── NarrativeEngine.php
│   │   │   ├── NarrativeQualityValidator.php
│   │   │   ├── NarrativeStyleEngine.php
│   │   │   ├── OpportunitiesNarrativeBuilder.php
│   │   │   ├── PlanetarySymbolEngine.php
│   │   │   ├── PlanetConditionEngine.php
│   │   │   ├── PlanetNature.php
│   │   │   ├── PlanetResolver.php
│   │   │   ├── PlanetStrengthEngine.php
│   │   │   ├── RetrogradeEngine.php
│   │   │   ├── RuleRegistry.php
│   │   │   ├── SignUtils.php
│   │   │   ├── SolarConditionEngine.php
│   │   │   ├── SourceCollector.php
│   │   │   ├── StelliumDetector.php
│   │   │   ├── StelliumIntegrationEngine.php
│   │   │   ├── TextTemplates.php
│   │   │   ├── ThemeAggregator.php
│   │   │   ├── ThemeMap.php
│   │   │   ├── ThemeNarrativeBuilder.php
│   │   │   ├── ThemePolarityAggregator.php
│   │   │   ├── ThemePolarityEngine.php
│   │   │   ├── ThemePresenter.php
│   │   │   ├── ThemeProfileBuilder.php
│   │   │   ├── ThemeRating.php
│   │   │   └── ThemeSummaryNarrativeBuilder.php
│   │   ├── AnnualForecastEngine.php
│   │   ├── AstroUtils.php
│   │   ├── auth_header.php*
│   │   ├── Auth.php*
│   │   ├── bootstrap.php*
│   │   ├── CuspidiUtils.php
│   │   ├── FiltroEsclusione.php*
│   │   ├── header_nav.php
│   │   ├── RicercaPageData.php
│   │   ├── RicercaRSAirportRepository.php
│   │   ├── RicercaRSDeduplicator.php
│   │   ├── RicercaRSExclusionFilter.php
│   │   ├── RicercaRSFilters.php
│   │   ├── RicercaRSPlanetHouseAssigner.php
│   │   ├── RicercaRSResultBuilder.php
│   │   ├── RicercaRSThemeBuilder.php
│   │   ├── RicercaRSTopK.php
│   │   ├── RsmAtlas.php
│   │   ├── RuleEngine.php*
│   │   ├── search_engine.php*
│   │   ├── SoggettoRepository.php
│   │   └── SweCalc.php
│   ├── js/
│   │   ├── app.js
│   │   ├── header_nav.js
│   │   ├── ricerca_astri.js
│   │   ├── ricerca_filtri_geo.js
│   │   ├── ricerca_paginazione.js
│   │   ├── rl.js
│   │   ├── rs_alert.js
│   │   ├── svg_zoom.js*
│   │   └── zodiac_wheel.js
│   ├── tests/
│   │   ├── cases/
│   │   │   ├── rs_lorenzo_2026_newyork.json
│   │   │   ├── rs_lorenzo_2026_roma.json
│   │   │   └── rs_lorenzo_2026_tokyo.json
│   │   ├── fixtures/
│   │   │   └── rule_engine_freeze.json
│   │   ├── search/
│   │   │   ├── amore_2026.php
│   │   │   ├── casa_2026.php
│   │   │   ├── decima_2026.php
│   │   │   ├── denaro_2026.php
│   │   │   ├── griglia_amore_2026.php
│   │   │   ├── griglia_casa_2026.php
│   │   │   ├── griglia_decima_2026.php
│   │   │   ├── griglia_denaro_2026.php
│   │   │   ├── griglia_lavoro_2026.php
│   │   │   ├── griglia_salute_2026.php
│   │   │   ├── lavoro_2026.php
│   │   │   └── salute_2026.php
│   │   ├── rilocazione_newyork_1960.php
│   │   ├── rl_lorenzo_2026.php
│   │   ├── run.php
│   │   ├── run_v6_backup_restore_check.sh*
│   │   ├── run_v6_hardening.sh*
│   │   ├── run_v6_rc1_check.sh*
│   │   ├── run_v6_rc2_check.sh*
│   │   ├── run_v6_release_check.sh*
│   │   ├── search_auth.php
│   │   ├── test_annual_forecast_v3.php
│   │   ├── test_annual_forecast_v4.php
│   │   ├── test_annual_report_browser_print.php
│   │   ├── test_annual_report_determinism.php
│   │   ├── test_annual_report_dompdf_smoke.php
│   │   ├── test_annual_report_executive_summary.php
│   │   ├── test_annual_report_pdf_determinism.php
│   │   ├── test_annual_report.php
│   │   ├── test_annual_report_print_renderer.php
│   │   ├── test_annual_report_print_sanitizer.php
│   │   ├── test_annual_report_real_cases.php
│   │   ├── test_annual_report_schema.php
│   │   ├── test_annual_summary_builder.php
│   │   ├── test_api_authenticated_contract.php
│   │   ├── test_api_unauthenticated_contract.php
│   │   ├── test_astronomical_backend_architecture.php
│   │   ├── test_atlanta_decima_diagnostica.php
│   │   ├── test_composer_dependencies.php
│   │   ├── test_conclusion_narrative_builder.php
│   │   ├── test_configuration_security.php
│   │   ├── test_cross_dynamics_builder.php
│   │   ├── test_database_environment.php
│   │   ├── test_executive_summary_narrative.php
│   │   ├── test_final_narrative_deduplication.php
│   │   ├── test_forecast_house_boundaries.php
│   │   ├── test_forecast_json_contract.php
│   │   ├── test_forecast_performance_budget.php
│   │   ├── test_forecast_real_rs.php
│   │   ├── test_forecast_v3.php
│   │   ├── test_gauquelin.php
│   │   ├── test_narrative_quality_duplicates.php
│   │   ├── test_narrative_style_engine.php
│   │   ├── test_print_report_css.php
│   │   ├── test_regression_v3.php
│   │   ├── test_ricerca_decima_rule_map.php
│   │   ├── test_rsm_dedup_sequence.php
│   │   ├── test_rsm_location_repository.php
│   │   ├── test_rsm_repository_pagination.php
│   │   ├── test_rsm_result_builder.php
│   │   ├── test_rsm_top_k.php
│   │   ├── test_rule_0001.php
│   │   ├── test_rule_0002.php
│   │   ├── test_rule_0003.php
│   │   ├── test_rule_0004.php
│   │   ├── test_rule_0005.php
│   │   ├── test_rule_0006.php
│   │   ├── test_rule_0007.php
│   │   ├── test_rule_0008.php
│   │   ├── test_rule_0009.php
│   │   ├── test_rule_0010.php
│   │   ├── test_rule_0011.php
│   │   ├── test_rule_0012.php
│   │   ├── test_rule_0013.php
│   │   ├── test_rule_0014.php
│   │   ├── test_rule_0015.php
│   │   ├── test_rule_0016.php
│   │   ├── test_rule_0017.php
│   │   ├── test_rule_0018.php
│   │   ├── test_rule_0019.php
│   │   ├── test_rule_0020.php
│   │   ├── test_rule_0021.php
│   │   ├── test_rule_0022.php
│   │   ├── test_rule_0023.php
│   │   ├── test_rule_0024.php
│   │   ├── test_rule_0025.php
│   │   ├── test_rule_0026.php
│   │   ├── test_rule_0027.php
│   │   ├── test_rule_0028.php
│   │   ├── test_rule_0029.php
│   │   ├── test_rule_0030.php
│   │   ├── test_rule_0031.php
│   │   ├── test_rule_0032.php
│   │   ├── test_rule_0033.php
│   │   ├── test_rule_0034.php
│   │   ├── test_rule_0035.php
│   │   ├── test_rule_0036.php
│   │   ├── test_rule_0037.php
│   │   ├── test_rule_0038.php
│   │   ├── test_rule_0039.php
│   │   ├── test_rule_0040.php
│   │   ├── test_rule_0041.php
│   │   ├── test_rule_0042.php
│   │   ├── test_rule_0043.php
│   │   ├── test_rule_0044.php
│   │   ├── test_rule_0045.php
│   │   ├── test_rule_0046.php
│   │   ├── test_rule_0047.php
│   │   ├── test_rule_0048.php
│   │   ├── test_rule_0049.php
│   │   ├── test_rule_0050.php
│   │   ├── test_rule_0051.php
│   │   ├── test_rule_0052.php
│   │   ├── test_rule_0053.php
│   │   ├── test_rule_0054.php
│   │   ├── test_rule_0055.php
│   │   ├── test_rule_0056.php
│   │   ├── test_rule_0057.php
│   │   ├── test_rule_0058.php
│   │   ├── test_rule_0059.php
│   │   ├── test_rule_0060.php
│   │   ├── test_rule_0061.php
│   │   ├── test_rule_0062.php
│   │   ├── test_rule_0063.php
│   │   ├── test_rule_0064.php
│   │   ├── test_rule_0065.php
│   │   ├── test_rule_0066.php
│   │   ├── test_rule_0067.php
│   │   ├── test_rule_0068.php
│   │   ├── test_rule_0069.php
│   │   ├── test_rule_0070.php
│   │   ├── test_rule_0071.php
│   │   ├── test_rule_0072.php
│   │   ├── test_rule_0073.php
│   │   ├── test_rule_0074.php
│   │   ├── test_rule_0075.php
│   │   ├── test_rule_0076.php
│   │   ├── test_rule_0077.php
│   │   ├── test_rule_0078.php
│   │   ├── test_rule_0079.php
│   │   ├── test_rule_0080.php
│   │   ├── test_rule_0081.php
│   │   ├── test_rule_0082.php
│   │   ├── test_rule_0083.php
│   │   ├── test_rule_0084.php
│   │   ├── test_rule_0085.php
│   │   ├── test_rule_0086.php
│   │   ├── test_rule_0087.php
│   │   ├── test_rule_0088.php
│   │   ├── test_rule_0089.php
│   │   ├── test_rule_0090.php
│   │   ├── test_rule_0091.php
│   │   ├── test_rule_0092.php
│   │   ├── test_rule_0093.php
│   │   ├── test_rule_0094.php
│   │   ├── test_rule_0095.php
│   │   ├── test_rule_0096.php
│   │   ├── test_rule_0097.php
│   │   ├── test_rule_0098.php
│   │   ├── test_rule_0099.php
│   │   ├── test_rule_0100.php
│   │   ├── test_rule_0101.php
│   │   ├── test_rule_0102.php
│   │   ├── test_rule_0103.php
│   │   ├── test_rule_0104.php
│   │   ├── test_rule_0105.php
│   │   ├── test_rule_0106.php
│   │   ├── test_rule_0107.php
│   │   ├── test_rule_0108.php
│   │   ├── test_rule_0109.php
│   │   ├── test_rule_0110.php
│   │   ├── test_rule_0111.php
│   │   ├── test_rule_0112.php
│   │   ├── test_rule_0113.php
│   │   ├── test_rule_0114.php
│   │   ├── test_rule_0115.php
│   │   ├── test_rule_0116.php
│   │   ├── test_rule_0117.php
│   │   ├── test_rule_0118.php
│   │   ├── test_rule_0119.php
│   │   ├── test_rule_0120.php
│   │   ├── test_rule_engine_freeze.php
│   │   ├── test_runtime_environment.php
│   │   └── test_theme_summary_narrative.php
│   ├── admin_utenti.php
│   ├── cambia_password.php
│   ├── compare_ril.php
│   ├── compare_rs.php
│   ├── composer.json
│   ├── composer.lock
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── ricerca.php
│   ├── rilocazione.php
│   ├── rl.php
│   ├── rs.php*
│   ├── stampa.php
│   └── tema.php*
├── airports.csv
├── astro-dss-source.dump
├── docker-compose.production.yml
├── docker-compose.yml
├── Dockerfile
├── .env
├── .env.example
├── .gitignore
├── struttura_app.md
└── STRUTTURA_astrolab.md
```
