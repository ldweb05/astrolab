<?php
declare(strict_types=1);

/**
 * Costruisce i filtri utilizzabili dal ramo località partendo dai filtri
 * storici degli aeroporti.
 *
 * @return array{
 *   whereAeroporti: array,
 *   paramsAeroporti: array,
 *   whereLocalita: array,
 *   paramsLocalita: array,
 *   haFiltroGeografico: bool
 * }
 */
function preparaFiltriLocalita(array $where, array $params): array
{
    $whereAeroporti = $where;
    $paramsAeroporti = $params;

    $whereLocalita = ['attivo = true'];
    $paramsLocalita = [];
    $haFiltroGeografico = false;
    $indiceParametro = 0;

    foreach ($where as $clausola) {
        $numeroParametri = substr_count($clausola, '?');
        $parametriClausola = array_slice(
            $params,
            $indiceParametro,
            $numeroParametri
        );
        $indiceParametro += $numeroParametri;

        $clausolaNormalizzata = trim($clausola);

        if ($clausolaNormalizzata === 'attivo = true') {
            continue;
        }

        if ($clausolaNormalizzata === 'nazione = ?') {
            $whereLocalita[] = 'iso_nazione = ?';
            $paramsLocalita = array_merge($paramsLocalita, $parametriClausola);
            $haFiltroGeografico = true;
            continue;
        }

        if (preg_match('/^nazione\s+IN\s*\((.+)\)$/i', $clausolaNormalizzata, $m)) {
            $whereLocalita[] = 'iso_nazione IN (' . $m[1] . ')';
            $paramsLocalita = array_merge($paramsLocalita, $parametriClausola);
            $haFiltroGeografico = true;
            continue;
        }

        if (
            $clausolaNormalizzata === 'longitudine >= ?' ||
            $clausolaNormalizzata === 'longitudine <= ?'
        ) {
            $whereLocalita[] = $clausolaNormalizzata;
            $paramsLocalita = array_merge($paramsLocalita, $parametriClausola);
            $haFiltroGeografico = true;
        }
    }

    return [
        'whereAeroporti'     => $whereAeroporti,
        'paramsAeroporti'    => $paramsAeroporti,
        'whereLocalita'      => $whereLocalita,
        'paramsLocalita'     => $paramsLocalita,
        'haFiltroGeografico' => $haFiltroGeografico,
    ];
}


/**
 * Recupera i punti geografici utilizzati dalla ricerca RS.
 *
 * Il ramo aeroporti mantiene integralmente i filtri storici.
 * Il ramo località riceve esclusivamente i filtri geografici compatibili:
 * - attivo
 * - nazione, tradotta in iso_nazione
 * - longitudine minima/massima
 *
 * Per sicurezza, nella prima fase le località vengono aggiunte solo quando
 * è presente almeno un filtro geografico. Non viene applicata alcuna soglia
 * di popolazione: anche i centri molto piccoli restano ricercabili.
 */

function recuperaAeroporti(PDO $pdo, array $where, array $params): array
{
    $filtri = preparaFiltriLocalita($where, $params);

    $whereAeroporti = $filtri['whereAeroporti'];
    $paramsAeroporti = $filtri['paramsAeroporti'];
    $whereLocalita = $filtri['whereLocalita'];
    $paramsLocalita = $filtri['paramsLocalita'];
    $haFiltroGeografico = $filtri['haFiltroGeografico'];

    if (!$haFiltroGeografico) {
        $sql = "
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                latitudine,
                longitudine
            FROM aeroporti
            WHERE " . implode(' AND ', $whereAeroporti) . "
            ORDER BY nazione, latitudine, longitudine
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsAeroporti);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $sql = "
        SELECT
            icao_code,
            iata_code,
            nome,
            citta,
            nazione,
            latitudine,
            longitudine
        FROM (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                latitudine,
                longitudine,
                0 AS priorita_origine
            FROM aeroporti
            WHERE " . implode(' AND ', $whereAeroporti) . "

            UNION ALL

            SELECT
                NULL AS icao_code,
                NULL AS iata_code,
                nome,
                citta,
                iso_nazione AS nazione,
                latitudine,
                longitudine,
                1 AS priorita_origine
            FROM localita
            WHERE " . implode(' AND ', $whereLocalita) . "
        ) AS punti_geografici
        ORDER BY
            priorita_origine,
            nazione,
            latitudine,
            longitudine
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($paramsAeroporti, $paramsLocalita));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Recupera e deduplica in PostgreSQL i punti geografici della ricerca RS.
 *
 * L'ordinamento replica quello consumato da deduplicaAeroporti():
 * il primo record incontrato per ciascun bucket viene mantenuto.
 *
 * @return array{aeroporti: array<int, array<string, mixed>>, totale_originale: int}
 */
function recuperaAeroportiDeduplicati(
    PDO $pdo,
    array $where,
    array $params,
    float $bucketLat,
    float $bucketLon
): array {
    $filtri = preparaFiltriLocalita($where, $params);

    $whereAeroporti = $filtri['whereAeroporti'];
    $paramsAeroporti = $filtri['paramsAeroporti'];
    $whereLocalita = $filtri['whereLocalita'];
    $paramsLocalita = $filtri['paramsLocalita'];
    $haFiltroGeografico = $filtri['haFiltroGeografico'];

    $ramiSql = "
        SELECT
            icao_code,
            iata_code,
            nome,
            citta,
            nazione,
            latitudine,
            longitudine,
            0 AS priorita_origine
        FROM aeroporti
        WHERE " . implode(' AND ', $whereAeroporti);

    $parametriQuery = $paramsAeroporti;

    if ($haFiltroGeografico) {
        $ramiSql .= "

            UNION ALL

            SELECT
                NULL AS icao_code,
                NULL AS iata_code,
                nome,
                citta,
                iso_nazione AS nazione,
                latitudine,
                longitudine,
                1 AS priorita_origine
            FROM localita
            WHERE " . implode(' AND ', $whereLocalita);

        $parametriQuery = array_merge(
            $parametriQuery,
            $paramsLocalita
        );
    }

    $sql = "
        WITH punti_geografici AS MATERIALIZED (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                latitudine,
                longitudine,
                priorita_origine
            FROM (
                {$ramiSql}
            ) AS sorgenti
            ORDER BY
                priorita_origine,
                nazione,
                latitudine,
                longitudine
        ),
        punti_ordinati AS MATERIALIZED (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                latitudine,
                longitudine,
                priorita_origine,
                ROW_NUMBER() OVER () AS ordine_origine
            FROM punti_geografici
        ),
        classificati AS (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                latitudine,
                longitudine,
                priorita_origine,
                ordine_origine,
                COUNT(*) OVER () AS totale_originale,
                ROW_NUMBER() OVER (
                    PARTITION BY
                        CASE
                            WHEN latitudine < 0
                                 AND ROUND(ABS(latitudine) / ?) = 0
                            THEN '-0'
                            ELSE ROUND(latitudine / ?)::text
                        END,
                        CASE
                            WHEN longitudine < 0
                                 AND ROUND(ABS(longitudine) / ?) = 0
                            THEN '-0'
                            ELSE ROUND(longitudine / ?)::text
                        END
                    ORDER BY ordine_origine
                ) AS posizione_bucket
            FROM punti_ordinati
        )
        SELECT
            icao_code,
            iata_code,
            nome,
            citta,
            nazione,
            latitudine,
            longitudine,
            totale_originale
        FROM classificati
        WHERE posizione_bucket = 1
        ORDER BY
            priorita_origine,
            nazione,
            latitudine,
            longitudine
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(
        $parametriQuery,
        [$bucketLat, $bucketLat, $bucketLon, $bucketLon]
    ));

    $righe = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totaleOriginale = $righe === []
        ? 0
        : (int)$righe[0]['totale_originale'];

    foreach ($righe as &$riga) {
        unset($riga['totale_originale']);
    }
    unset($riga);

    return [
        'aeroporti' => $righe,
        'totale_originale' => $totaleOriginale,
    ];
}
