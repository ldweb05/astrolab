<?php
declare(strict_types=1);

/**
 * Aggiunge un risultato mantenendo in memoria soltanto i migliori K.
 *
 * Con limite null conserva il comportamento storico e accumula tutti
 * i risultati. A parità di stelline vengono mantenuti i record incontrati
 * prima, coerentemente con l'ordinamento stabile applicato a fine ricerca.
 *
 * @param array<int, array<string, mixed>> $risultati
 * @param array<string, mixed> $nuovoRisultato
 */
function aggiungiRisultatoTopK(
    array &$risultati,
    array $nuovoRisultato,
    ?int $limite
): void {
    if ($limite === null) {
        $risultati[] = $nuovoRisultato;
        return;
    }

    if ($limite <= 0) {
        return;
    }

    if (count($risultati) < $limite) {
        $risultati[] = $nuovoRisultato;
        return;
    }

    // Punteggio V2 (unico sistema, vecchio 'stelline' rimosso - Fase 4)
    $stellineNuove = (int)($nuovoRisultato['v2_stelle_totali'] ?? 0);
    $stellineMinime = PHP_INT_MAX;
    $ultimoIndiceMinimo = null;

    foreach ($risultati as $indice => $risultato) {
        $stelline = (int)($risultato['v2_stelle_totali'] ?? 0);

        if ($stelline < $stellineMinime) {
            $stellineMinime = $stelline;
            $ultimoIndiceMinimo = $indice;
        } elseif ($stelline === $stellineMinime) {
            $ultimoIndiceMinimo = $indice;
        }
    }

    if ($stellineNuove <= $stellineMinime || $ultimoIndiceMinimo === null) {
        return;
    }

    unset($risultati[$ultimoIndiceMinimo]);
    $risultati = array_values($risultati);
    $risultati[] = $nuovoRisultato;
}
