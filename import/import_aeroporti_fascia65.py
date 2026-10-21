#!/usr/bin/env python3
"""
Import aeroporti nella fascia di latitudine 65°-66,5° (Nord e Sud)
===================================================================

Contesto (HANDOVER 2026-10-21, allineamento ad Aladino):
l'import originale della tabella `aeroporti` (da import/data/airports.csv,
OurAirports, 85.503 righe) aveva scartato tutti i punti con |lat| > 65°,
lasciandone 84.616. Mancavano cosi' Maniitsoq (GL), Akureyri, Egilsstadir,
Isafjordur (IS) e altri, che Aladino invece restituisce.

Questo script reimporta SOLO la fascia 65° < |lat| < 66,5°: oltre il Circolo
Polare (66,56°) le case Placido non sono definite e libswe ripiega in
silenzio su un altro sistema, quindi quei punti restano esclusi.

Regole ricostruite dall'import originale (0 errori su tutte le 84.616 righe):
  - icao_code  <- ident ; iata_code <- iata_code ; nome <- name
  - citta      <- municipality ; nazione = iso_nazione <- iso_country
  - altitudine <- elevation_ft (piedi, cosi' com'e')
  - attivo  = tipo large/medium_airport, oppure small_airport/heliport/
              seaplane_base CON codice IATA; closed e balloonport mai
  - militare = nome contiene "air base", "military" o "air force"

Lo script e' idempotente (non inserisce ident gia' presenti) e lavora in
una sola transazione. Legge da stdin le convenzioni dei campi vuoti gia'
usate nel DB (NULL oppure ''/0) e scrive l'SQL su stdout.

Uso (dalla root del repo, sul Raspberry Pi o sulla VPS):

  docker compose exec -T astrolab-db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -At -F"|" -c "SELECT COUNT(*) FILTER (WHERE iata_code IS NULL), COUNT(*) FILTER (WHERE iata_code = '"''"'), COUNT(*) FILTER (WHERE citta IS NULL), COUNT(*) FILTER (WHERE citta = '"''"'), COUNT(*) FILTER (WHERE altitudine IS NULL), COUNT(*) FILTER (WHERE altitudine = 0) FROM aeroporti;"' \
    | python3 import/import_aeroporti_fascia65.py \
    | docker compose exec -T astrolab-db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -v ON_ERROR_STOP=1 -q'

Prima di eseguirlo creare sempre un backup della tabella:
  docker compose exec -T astrolab-db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -t aeroporti --clean --if-exists' > ~/backup_astrolab/aeroporti_$(date +%Y%m%d_%H%M%S).sql
"""
import csv
import re
import sys
from pathlib import Path

CSV_PATH = Path(__file__).resolve().parent / "data" / "airports.csv"
LAT_MIN, LAT_MAX = 65.0, 66.5
MIL = re.compile(r"air base|military|air force", re.I)


def q(s: str) -> str:
    return "'" + s.replace("'", "''") + "'"


def main() -> None:
    conv = sys.stdin.read().strip().split("|")
    if len(conv) != 6:
        sys.exit("Convenzioni DB non lette da stdin: vedere l'uso nel docstring")
    iata_null, iata_vuoto, citta_null, citta_vuota, alt_null, alt_zero = map(int, conv)

    def testo(v: str, usa_null: bool) -> str:
        return q(v) if v != "" else ("NULL" if usa_null else "''")

    righe = []
    with open(CSV_PATH, encoding="utf-8") as f:
        for r in csv.DictReader(f):
            lat = float(r["latitude_deg"])
            if not (LAT_MIN < abs(lat) < LAT_MAX):
                continue
            attivo = r["type"] in ("large_airport", "medium_airport") or (
                r["type"] in ("small_airport", "heliport", "seaplane_base") and r["iata_code"] != ""
            )
            if r["elevation_ft"] != "":
                alt = str(int(float(r["elevation_ft"])))
            else:
                alt = "NULL" if alt_null > alt_zero else "0"
            righe.append("(" + ",".join([
                testo(r["iata_code"], iata_null > iata_vuoto),
                q(r["ident"]),
                q(r["name"]),
                testo(r["municipality"], citta_null > citta_vuota),
                q(r["iso_country"]),
                q(r["iso_country"]),
                f"{lat:.6f}",
                f"{float(r['longitude_deg']):.6f}",
                alt,
                q(r["type"]),
                "true" if MIL.search(r["name"]) else "false",
                "true" if attivo else "false",
            ]) + ")")

    if not righe:
        sys.exit("Nessuna riga nella fascia: CSV vuoto o percorso errato")

    print("BEGIN;")
    print("CREATE TEMP TABLE imp (iata_code varchar(10), icao_code varchar(10), nome varchar(200), "
          "citta varchar(100), nazione varchar(100), iso_nazione varchar(10), latitudine numeric(9,6), "
          "longitudine numeric(9,6), altitudine integer, tipo varchar(50), militare boolean, attivo boolean);")
    print("INSERT INTO imp VALUES\n" + ",\n".join(righe) + ";")
    print("INSERT INTO aeroporti (iata_code, icao_code, nome, citta, nazione, iso_nazione, latitudine, "
          "longitudine, altitudine, tipo, militare, attivo) "
          "SELECT i.* FROM imp i WHERE NOT EXISTS "
          "(SELECT 1 FROM aeroporti a WHERE a.icao_code = i.icao_code);")
    print(f"DO $$ BEGIN IF (SELECT COUNT(*) FROM imp i WHERE NOT EXISTS "
          f"(SELECT 1 FROM aeroporti a WHERE a.icao_code = i.icao_code)) <> 0 "
          f"THEN RAISE EXCEPTION 'Righe della fascia non importate: rollback'; END IF; END $$;")
    print("COMMIT;")
    print(f"\\echo 'Righe nella fascia {LAT_MIN}-{LAT_MAX} gestite: {len(righe)}'")
    print("SELECT COUNT(*) AS totale, MAX(latitudine) AS lat_max FROM aeroporti;")


if __name__ == "__main__":
    main()
