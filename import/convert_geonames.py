#!/usr/bin/env python3

import csv
import io
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DATA_DIR = ROOT / "import" / "data"
ARCHIVE = DATA_DIR / "allCountries.zip"
COUNTRIES_FILE = DATA_DIR / "countryInfo.txt"

if not ARCHIVE.is_file():
    raise SystemExit(f"Archivio mancante: {ARCHIVE}")

if not COUNTRIES_FILE.is_file():
    raise SystemExit(f"File nazioni mancante: {COUNTRIES_FILE}")

countries: dict[str, str] = {}

with COUNTRIES_FILE.open("r", encoding="utf-8", newline="") as handle:
    for raw_line in handle:
        if not raw_line.strip() or raw_line.startswith("#"):
            continue

        fields = raw_line.rstrip("\n").split("\t")
        if len(fields) < 5:
            continue

        iso_code = fields[0].strip().upper()
        country_name = fields[4].strip()

        if iso_code and country_name:
            countries[iso_code] = country_name

stdout = io.TextIOWrapper(
    sys.stdout.buffer,
    encoding="utf-8",
    newline="",
    write_through=True,
)

writer = csv.writer(stdout, lineterminator="\n")
writer.writerow([
    "codice",
    "nome",
    "citta",
    "nazione",
    "iso_nazione",
    "latitudine",
    "longitudine",
    "popolazione",
    "tipo",
    "fonte",
])

written = 0
skipped = 0

with zipfile.ZipFile(ARCHIVE) as archive:
    with archive.open("allCountries.txt", "r") as binary_handle:
        text_handle = io.TextIOWrapper(binary_handle, encoding="utf-8", newline="")

        for raw_line in text_handle:
            fields = raw_line.rstrip("\n").split("\t")

            if len(fields) < 19:
                skipped += 1
                continue

            geoname_id = fields[0].strip()
            name = fields[1].strip()
            ascii_name = fields[2].strip()
            latitude = fields[4].strip()
            longitude = fields[5].strip()
            feature_class = fields[6].strip()
            feature_code = fields[7].strip()
            iso_country = fields[8].strip().upper()
            population = fields[14].strip()

            if feature_class != "P":
                continue

            if not all((
                geoname_id,
                name,
                latitude,
                longitude,
                iso_country,
            )):
                skipped += 1
                continue

            try:
                latitude_value = float(latitude)
                longitude_value = float(longitude)
                population_value = int(population or "0")
            except ValueError:
                skipped += 1
                continue

            if not (-90 <= latitude_value <= 90):
                skipped += 1
                continue

            if not (-180 <= longitude_value <= 180):
                skipped += 1
                continue

            writer.writerow([
                f"GN-{geoname_id}",
                name,
                ascii_name or name,
                countries.get(iso_country, iso_country),
                iso_country,
                f"{latitude_value:.6f}",
                f"{longitude_value:.6f}",
                population_value,
                feature_code.lower() or "localita",
                "geonames",
            ])

            written += 1

print(
    f"GeoNames convertite: {written}; righe P escluse: {skipped}",
    file=sys.stderr,
)
