from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def replace_once(path: Path, old: str, new: str) -> None:
    text = path.read_text(encoding="utf-8")
    if old not in text:
        raise SystemExit(f"Blocco non trovato in {path}")
    path.write_text(text.replace(old, new, 1), encoding="utf-8")

# 1. Interfaccia: la modalità mista può essere già stata rimossa
# da un precedente tentativo parziale.
ricerca = ROOT / "www/ricerca.php"
ricerca_text = ricerca.read_text(encoding="utf-8")
ricerca_text = ricerca_text.replace(
    '<option value="aeroporti_e_localita">Aeroporti + località</option>\n',
    "",
    1,
)
ricerca.write_text(ricerca_text, encoding="utf-8")

# 2. API: accetta solo le due modalità definitive.
api = ROOT / "www/api/ricerca_stream_api.php"
api_text = api.read_text(encoding="utf-8")
api_text = api_text.replace(
    "$tipiLocalitaValidi = ['solo_aeroporti', 'aeroporti_e_localita', 'solo_localita'];",
    "$tipiLocalitaValidi = ['solo_aeroporti', 'solo_localita'];",
    1,
)
api.write_text(api_text, encoding="utf-8")

repo = ROOT / "www/includes/RicercaRSAirportRepository.php"
text = repo.read_text(encoding="utf-8")

text = text.replace(
    """    $usaAeroporti = $tipoLocalita !== 'solo_localita';
    $usaLocalita = $tipoLocalita === 'aeroporti_e_localita'
        || $tipoLocalita === 'solo_localita'
        || ($tipoLocalita === '' && $haFiltroGeografico);
""",
    """    $usaAeroporti = $tipoLocalita !== 'solo_localita';
    $usaLocalita = $tipoLocalita === 'solo_localita'
        || ($tipoLocalita === '' && $haFiltroGeografico);
""",
    1,
)

marker = "function recuperaAeroportiDeduplicati("
pos = text.index(marker)
prima, dedup = text[:pos], text[pos:]

dedup = dedup.replace(
    """                latitudine,
                longitudine,
                0 AS priorita_origine
""",
    """                latitudine,
                longitudine,
                'aeroporto'::VARCHAR(20) AS origine_punto,
                0 AS priorita_origine
""",
    1,
)

dedup = dedup.replace(
    """                latitudine,
                longitudine,
                1 AS priorita_origine
""",
    """                latitudine,
                longitudine,
                'localita'::VARCHAR(20) AS origine_punto,
                1 AS priorita_origine
""",
    1,
)

for needle in [
    """                longitudine,
                priorita_origine
""",
    """                longitudine,
                priorita_origine,
                ROW_NUMBER() OVER () AS ordine_origine
""",
    """                longitudine,
                priorita_origine,
                ordine_origine,
""",
]:
    replacement = needle.replace(
        "                priorita_origine",
        "                origine_punto,\n                priorita_origine",
        1,
    )
    if needle in dedup:
        dedup = dedup.replace(needle, replacement, 1)

dedup = dedup.replace(
    """                priorita_origine,
                ROW_NUMBER() OVER () AS ordine_origine
""",
    """                priorita_origine,
                ROW_NUMBER() OVER (
                    ORDER BY priorita_origine, nazione, latitudine, longitudine, nome, citta, icao_code, iata_code
                ) AS ordine_origine
""",
    1,
)

dedup = dedup.replace(
    """            longitudine,
            totale_originale
""",
    """            longitudine,
            origine_punto,
            totale_originale
""",
    1,
)

prima = prima.replace(
    """            priorita_origine,
            nazione,
            latitudine,
            longitudine
""",
    """            priorita_origine,
            nazione,
            latitudine,
            longitudine,
            nome,
            citta,
            icao_code,
            iata_code
""",
    1,
)

dedup = dedup.replace(
    """            priorita_origine,
            nazione,
            latitudine,
            longitudine
""",
    """            priorita_origine,
            nazione,
            latitudine,
            longitudine,
            nome,
            citta,
            icao_code,
            iata_code
""",
    1,
)

repo.write_text(prima + dedup, encoding="utf-8")

# 3. Builder: propaga il tipo esplicito del punto.
builder = ROOT / "www/includes/RicercaRSResultBuilder.php"
builder_text = builder.read_text(encoding="utf-8")
builder_text = builder_text.replace(
    """    'aeroporto_associato'    => $aero['aeroporto_associato'] ?? null,
    'lat'                    => $latA,
""",
    """    'aeroporto_associato'    => $aero['aeroporto_associato'] ?? null,
    'origine_punto'          => $aero['origine_punto'] ?? 'aeroporto',
    'lat'                    => $latA,
""",
    1,
)
builder.write_text(builder_text, encoding="utf-8")

# 4. Frontend: non dedurre più il tipo dalla presenza dei codici.
frontend = ROOT / "www/ricerca.php"
frontend_text = frontend.read_text(encoding="utf-8")
frontend_text = frontend_text.replace(
    "const isLocalita = !r.iata && !r.icao && !r.aeroporto_associato;",
    "const isLocalita = r.origine_punto === 'localita';",
    1,
)
frontend.write_text(frontend_text, encoding="utf-8")

# 5. Test: rimuove il caso relativo alla modalità mista.
test = ROOT / "www/tests/test_rsm_location_repository.php"
test_text = test.read_text(encoding="utf-8")
test_text = test_text.replace(
    """    'v3_localita_multinazione' => [
        'where' => [
            'attivo = true',
            "tipo IN ('large_airport','medium_airport','small_airport')",
            'nazione IN (?,?)',
            'longitudine >= ?',
            'longitudine <= ?',
        ],
        'params' => ['IT', 'FR', -6.0, 19.0],
        'attende_localita' => true,
        'tipo_localita' => 'aeroporti_e_localita',
    ],
""",
    "",
    1,
)
test.write_text(test_text, encoding="utf-8")

print("Migrazione completata.")
