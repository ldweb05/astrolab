./
├── airports.csv
├── backup_astrologia.dump
├── bkup-1-7-2026_db.sql
├── docker-compose.yml*
├── docker-compose.yml.bak_20260710_094533*
├── docker-compose.yml.old
├── Dockerfile
├── .env
├── .env.bak_20260710_094533
├── .env.example
├── .env.production
├── .gitignore
├── migration.sql
├── php-config/
│   └── php.ini
├── postgres-data/  [error opening dir]
├── struttura_app.md
├── struttura_db.sql
├── tests/
│   └── snapshots/
│       └── swecalc/
├── tools/
│   ├── generate_doc.js
│   ├── package.json
│   ├── package-lock.json
│   └── progetto_astrologia_overview.docx
└── www/
    ├── admin_utenti.php
    ├── api/
    │   ├── cuspidi_search_api.php
    │   ├── luoghi_api.php
    │   ├── ricerca_api.php*
    │   ├── ricerca_griglia_api.php*
    │   ├── ._ricerca_stream_api.php*
    │   ├── ricerca_stream_api.php
    │   ├── riloc_angolari_api.php
    │   ├── rl_api.php
    │   ├── rs_alert_api.php
    │   ├── rs_api.php
    │   ├── sensibilita_api.php
    │   ├── session_api.php
    │   ├── sessioni_api.php
    │   ├── soggetti_api.php
    │   ├── stampa_pdf_api.php
    │   └── tema_api.php
    ├── cambia_password.php
    ├── composer.json
    ├── composer.lock
    ├── config/
    │   └── previsione-annuale/
    │       └── config.json
    ├── css/
    │   ├── print.css
    │   └── style.css
    ├── includes/
    │   ├── AnnualForecastEngine.php
    │   ├── AstroUtils.php
    │   ├── atlas/
    │   │   ├── AtlasLoader.php
    │   │   ├── JupiterAtlas.php
    │   │   ├── MarsAtlas.php
    │   │   ├── MercuryAtlas.php
    │   │   ├── MoonAtlas.php
    │   │   ├── NeptuneAtlas.php
    │   │   ├── PlutoAtlas.php
    │   │   ├── RsmAtlas.php
    │   │   ├── SaturnAtlas.php
    │   │   ├── SunAtlas.php
    │   │   ├── ThemeCatalog.php
    │   │   ├── UranusAtlas.php
    │   │   └── VenusAtlas.php
    │   ├── auth_header.php*
    │   ├── Auth.php*
    │   ├── bootstrap.php*
    │   ├── CuspidiUtils.php
    │   ├── FiltroEsclusione.php*
    │   ├── forecast/
    │   │   ├── AARuleEngine.php
    │   │   ├── AARules.php
    │   │   ├── AdvancedContextEngine.php
    │   │   ├── AdvancedForecastEngine.php
    │   │   ├── AdvancedThemeAggregator.php
    │   │   ├── AngularPowerEngine.php
    │   │   ├── AspectEngine.php
    │   │   ├── AspectInterpretationEngine.php
    │   │   ├── AspectScoreEngine.php
    │   │   ├── DignityEngine.php
    │   │   ├── DignityIntegrationEngine.php
    │   │   ├── DominantThemeEngine.php
    │   │   ├── FinalNarrativeEngine.php
    │   │   ├── ForecastContextEngine.php
    │   │   ├── ForecastEngineV2.php
    │   │   ├── ForecastEngineV3.php
    │   │   ├── HouseDominanceEngine.php
    │   │   ├── NarrativeComposer.php
    │   │   ├── NarrativeEngine.php
    │   │   ├── PlanetarySymbolEngine.php
    │   │   ├── PlanetNature.php
    │   │   ├── PlanetResolver.php
    │   │   ├── PlanetStrengthEngine.php
    │   │   ├── RetrogradeEngine.php
    │   │   ├── SignUtils.php
    │   │   ├── SolarConditionEngine.php
    │   │   ├── SourceCollector.php
    │   │   ├── StelliumDetector.php
    │   │   ├── StelliumIntegrationEngine.php
    │   │   ├── TextTemplates.php
    │   │   ├── ThemeAggregator.php
    │   │   ├── ThemeMap.php
    │   │   ├── ThemePolarityAggregator.php
    │   │   ├── ThemePolarityEngine.php
    │   │   ├── ThemePresenter.php
    │   │   └── ThemeRating.php
    │   ├── header_nav.php
    │   ├── RicercaPageData.php
    │   ├── RicercaRSAirportRepository.php
    │   ├── RicercaRSDeduplicator.php
    │   ├── RicercaRSExclusionFilter.php
    │   ├── RicercaRSFilters.php
    │   ├── RicercaRSPlanetHouseAssigner.php
    │   ├── RicercaRSResultBuilder.php
    │   ├── RicercaRSThemeBuilder.php
    │   ├── RsmAtlas.php
    │   ├── RuleEngine.php*
    │   ├── search_engine.php*
    │   ├── SoggettoRepository.php
    │   └── SweCalc.php
    ├── index.php
    ├── js/
    │   ├── app.js
    │   ├── ._ricerca_astri.js*
    │   ├── ricerca_astri.js
    │   ├── ._ricerca_filtri_geo.js*
    │   ├── ricerca_filtri_geo.js
    │   ├── ricerca_paginazione.js
    │   ├── rl.js
    │   ├── rs_alert.js
    │   ├── svg_zoom.js*
    │   └── zodiac_wheel.js
    ├── login.php
    ├── logout.php
    ├── migrations/
    │   ├── 000_stato_iniziale.sql
    │   ├── 001_dati_database.sql
    │   └── 001_struttura_database.sql
    ├── ricerca.php
    ├── rilocazione.php
    ├── rl.php
    ├── rs.php*
    ├── stampa.php
    ├── tema.php*
    └── tests/
        ├── cases/
        │   ├── rs_lorenzo_2026_newyork.json
        │   ├── rs_lorenzo_2026_roma.json
        │   └── rs_lorenzo_2026_tokyo.json
        ├── expected/
        ├── rilocazione_newyork_1960.php
        ├── rl_lorenzo_2026.php
        ├── run.php
        ├── search/
        │   ├── amore_2026.php
        │   ├── casa_2026.php
        │   ├── decima_2026.php
        │   ├── denaro_2026.php
        │   ├── griglia_amore_2026.php
        │   ├── griglia_casa_2026.php
        │   ├── griglia_decima_2026.php
        │   ├── griglia_denaro_2026.php
        │   ├── griglia_lavoro_2026.php
        │   ├── griglia_salute_2026.php
        │   ├── lavoro_2026.php
        │   └── salute_2026.php
        ├── test_annual_forecast_v3.php
        ├── test_forecast_real_rs.php
        ├── test_forecast_v3.php
        └── test_gauquelin.php

21 directories, 157 files
