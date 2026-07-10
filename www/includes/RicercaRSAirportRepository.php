<?php
declare(strict_types=1);

function recuperaAeroporti(PDO $pdo, array $where, array $params): array
{
    $sql = "SELECT icao_code, iata_code, nome, citta, nazione,
                   latitudine, longitudine
            FROM aeroporti
            WHERE " . implode(' AND ', $where) . "
            ORDER BY nazione, latitudine, longitudine";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

