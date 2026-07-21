<?php
declare(strict_types=1);

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
            $paramsLocalita = array_merge(
                $paramsLocalita,
                $parametriClausola
            );
            $haFiltroGeografico = true;
            continue;
        }

        if (preg_match('/^nazione\s+IN\s*\((.+)\)$/i', $clausolaNormalizzata, $match)) {
            $whereLocalita[] = 'iso_nazione IN (' . $match[1] . ')';
            $paramsLocalita = array_merge(
                $paramsLocalita,
                $parametriClausola
            );
            $haFiltroGeografico = true;
            continue;
        }

        if (
            $clausolaNormalizzata === 'longitudine >= ?'
            || $clausolaNormalizzata === 'longitudine <= ?'
        ) {
            $whereLocalita[] = $clausolaNormalizzata;
            $paramsLocalita = array_merge(
                $paramsLocalita,
                $parametriClausola
            );
            $haFiltroGeografico = true;
        }
    }

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
