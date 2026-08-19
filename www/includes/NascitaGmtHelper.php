<?php
/**
 * Helper per il calcolo corretto della data e ora GMT dalla data/ora locale e offset.
 * Gestisce il cambio di giorno quando l'ora locale è precedente all'offset.
 */

/**
 * Calcola la data e ora GMT corrette a partire da data locale, ora locale e offset GMT.
 *
 * @param string $data_nascita Data di nascita in formato Y-m-d
 * @param string $ora_nascita Ora di nascita in formato H:i:s o H:i
 * @param float $offset_gmt Offset GMT (es. +2.00 per Italia estate)
 * @return array ['data_gmt' => string, 'ora_gmt' => string]
 */
function calcolaDataOraGmtCorretta(string $data_nascita, string $ora_nascita, float $offset_gmt): array
{
    // Normalizza l'ora aggiungendo i secondi se mancanti
    if (strlen($ora_nascita) == 5) {
        $ora_nascita .= ':00';
    }

    // Crea un oggetto DateTime con la data/ora locale
    $datetime_locale = new DateTime($data_nascita . ' ' . $ora_nascita);

    // Sottrai l'offset per ottenere l'ora GMT.
    // Se offset è +2, sottraggo 2 ore per tornare a GMT.
    // Convertito in secondi (non ore intere) per gestire correttamente
    // offset frazionari reali (es. +5.5 India, +3.5 Iran, -3.5 Terranova).
    $offset_secondi = (int)round(abs($offset_gmt) * 3600);
    $intervallo = new DateInterval('PT' . $offset_secondi . 'S');

    if ($offset_gmt >= 0) {
        $datetime_gmt = $datetime_locale->sub($intervallo);
    } else {
        $datetime_gmt = $datetime_locale->add($intervallo);
    }

    return [
        'data_gmt' => $datetime_gmt->format('Y-m-d'),
        'ora_gmt' => $datetime_gmt->format('H:i:s')
    ];
}
