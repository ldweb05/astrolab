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
                tipo,
                NULL::BIGINT AS popolazione,
                nome AS aeroporto_associato,
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
            tipo,
            popolazione,
            aeroporto_associato,
            latitudine,
            longitudine
        FROM (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                tipo,
                NULL::BIGINT AS popolazione,
                nome AS aeroporto_associato,
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
                tipo,
                popolazione,
                NULL::VARCHAR(200) AS aeroporto_associato,
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
            longitudine,
            nome,
            citta,
            icao_code,
            iata_code
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
    float $bucketLon,
    string $tipoLocalita = '',
    ?int $limite = null,
    int $offset = 0
): array {
    $filtri = preparaFiltriLocalita($where, $params);

    $whereAeroporti = $filtri['whereAeroporti'];
    $paramsAeroporti = $filtri['paramsAeroporti'];
    $whereLocalita = $filtri['whereLocalita'];
    $paramsLocalita = $filtri['paramsLocalita'];
    $haFiltroGeografico = $filtri['haFiltroGeografico'];

    $usaAeroporti = $tipoLocalita !== 'localita';
    $usaLocalita = $tipoLocalita === 'localita'
        || ($tipoLocalita === '' && $haFiltroGeografico);

    $ricercaLocalita = $tipoLocalita === 'localita';

    $rami = [];
    $parametriQuery = [];

    if ($usaAeroporti) {
        $rami[] = "
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                tipo,
                NULL::BIGINT AS popolazione,
                nome AS aeroporto_associato,
                latitudine,
                longitudine,
                'aeroporto'::VARCHAR(20) AS origine_punto,
                0 AS priorita_origine
            FROM aeroporti
            WHERE " . implode(' AND ', $whereAeroporti);

        $parametriQuery = array_merge(
            $parametriQuery,
            $paramsAeroporti
        );
    }

    if ($usaLocalita) {
        $rami[] = "
            SELECT
                NULL AS icao_code,
                NULL AS iata_code,
                nome,
                citta,
                iso_nazione AS nazione,
                tipo,
                popolazione,
                NULL::VARCHAR(200) AS aeroporto_associato,
                latitudine,
                longitudine,
                'localita'::VARCHAR(20) AS origine_punto,
                1 AS priorita_origine
            FROM localita
            WHERE " . implode(' AND ', $whereLocalita);

        $parametriQuery = array_merge(
            $parametriQuery,
            $paramsLocalita
        );
    }

    $ramiSql = implode("\n\n            UNION ALL\n", $rami);

    $sql = "
        WITH punti_geografici AS MATERIALIZED (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                tipo,
                popolazione,
                aeroporto_associato,
                latitudine,
                longitudine,
                origine_punto,
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
                tipo,
                popolazione,
                aeroporto_associato,
                latitudine,
                longitudine,
                origine_punto,
                priorita_origine,
                ROW_NUMBER() OVER (
                    ORDER BY priorita_origine, nazione, latitudine, longitudine, nome, citta, icao_code, iata_code
                ) AS ordine_origine
            FROM punti_geografici
        ),
        classificati AS (
            SELECT
                icao_code,
                iata_code,
                nome,
                citta,
                nazione,
                tipo,
                popolazione,
                aeroporto_associato,
                latitudine,
                longitudine,
                origine_punto,
                priorita_origine,
                ordine_origine,
                COUNT(*) OVER () AS totale_originale,
                ROW_NUMBER() OVER (
                    PARTITION BY
                        CASE
                            WHEN ? = 1 THEN nazione
                            WHEN latitudine < 0
                                 AND ROUND(ABS(latitudine) / ?) = 0
                            THEN '-0'
                            ELSE ROUND(latitudine / ?)::text
                        END,
                        CASE
                            WHEN ? = 1 THEN COALESCE(
                                NULLIF(BTRIM(citta), ''),
                                NULLIF(BTRIM(nome), ''),
                                ''
                            )
                            WHEN longitudine < 0
                                 AND ROUND(ABS(longitudine) / ?) = 0
                            THEN '-0'
                            ELSE ROUND(longitudine / ?)::text
                        END,
                        CASE WHEN ? = 1 THEN latitudine ELSE NULL END,
                        CASE WHEN ? = 1 THEN longitudine ELSE NULL END
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
            tipo,
            popolazione,
            aeroporto_associato,
            latitudine,
            longitudine,
            origine_punto,
            totale_originale
        FROM classificati
        WHERE posizione_bucket = 1
        ORDER BY
            priorita_origine,
            nazione,
            latitudine,
            longitudine,
            nome,
            citta,
            icao_code,
            iata_code
    ";

    if ($limite !== null) {
        $limite = max(1, $limite);
        $offset = max(0, $offset);
        $sql .= " LIMIT {$limite} OFFSET {$offset}";
    }

    $stmt = $pdo->prepare($sql);
    $modalitaLocalita = $ricercaLocalita ? 1 : 0;

    $stmt->execute(array_merge(
        $parametriQuery,
        [
            $modalitaLocalita,
            $bucketLat,
            $bucketLat,
            $modalitaLocalita,
            $bucketLon,
            $bucketLon,
            $modalitaLocalita,
            $modalitaLocalita,
        ]
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


/**
 * Associa i codici aeroportuali alle sole località finali della ricerca.
 *
 * La funzione non modifica l'ordine né il numero dei risultati ricevuti.
 * Gli aeroporti vengono caricati in un'unica query limitata alle nazioni
 * presenti nell'elenco finale.
 *
 * @param array<int, array<string, mixed>> $risultati
 * @return array<int, array<string, mixed>>
 */
function arricchisciLocalitaConAeroporti(
    PDO $pdo,
    array $risultati
): array {
    if ($risultati === []) {
        return $risultati;
    }

    $nazioni = [];

    foreach ($risultati as $risultato) {
        if (($risultato['origine_punto'] ?? '') !== 'localita') {
            continue;
        }

        $nazione = strtoupper(trim((string)($risultato['nazione'] ?? '')));

        if ($nazione !== '') {
            $nazioni[$nazione] = true;
        }
    }

    if ($nazioni === []) {
        return $risultati;
    }

    $codiciNazione = array_keys($nazioni);
    $placeholders = implode(
        ', ',
        array_fill(0, count($codiciNazione), '?')
    );

    $sql = "
        SELECT
            icao_code,
            iata_code,
            nome,
            citta,
            iso_nazione
        FROM aeroporti
        WHERE attivo = true
          AND iso_nazione IN ({$placeholders})
        ORDER BY
            iso_nazione,
            citta,
            (iata_code IS NULL OR iata_code = ''),
            (icao_code IS NULL OR icao_code = ''),
            nome,
            icao_code,
            iata_code
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($codiciNazione);

    $normalizza = static function (string $valore): string {
        $valore = trim($valore);

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($valore, 'UTF-8');
        }

        return strtolower($valore);
    };

    $aeroportiPerLocalita = [];

    while ($aeroporto = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nazione = strtoupper(trim((string)$aeroporto['iso_nazione']));
        $citta = $normalizza((string)$aeroporto['citta']);

        if ($nazione === '' || $citta === '') {
            continue;
        }

        $chiave = $nazione . '|' . $citta;

        if (!isset($aeroportiPerLocalita[$chiave])) {
            $aeroportiPerLocalita[$chiave] = $aeroporto;
        }
    }

    foreach ($risultati as &$risultato) {
        if (($risultato['origine_punto'] ?? '') !== 'localita') {
            continue;
        }

        $nazione = strtoupper(trim((string)($risultato['nazione'] ?? '')));
        $citta = $normalizza((string)($risultato['citta'] ?? ''));

        if ($nazione === '' || $citta === '') {
            continue;
        }

        $chiave = $nazione . '|' . $citta;
        $aeroporto = $aeroportiPerLocalita[$chiave] ?? null;

        if ($aeroporto === null) {
            continue;
        }

        $risultato['icao'] = $aeroporto['icao_code'];
        $risultato['iata'] = $aeroporto['iata_code'];
        $risultato['aeroporto_associato'] = $aeroporto['nome'];
    }
    unset($risultato);

    return $risultati;
}
