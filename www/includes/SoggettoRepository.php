<?php
declare(strict_types=1);

/**
 * Carica un soggetto dal database tramite ID.
 * Non effettua controlli di autorizzazione: è una utility condivisa
 * per componenti interni (CLI, test automatici, ecc.).
 */
function caricaSoggettoById(PDO $pdo, int $soggettoId): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM soggetti WHERE id = ?");
    $stmt->execute([$soggettoId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}
