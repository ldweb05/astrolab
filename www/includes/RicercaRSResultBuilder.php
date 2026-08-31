<?php
declare(strict_types=1);

function costruisciRisultatoRicercaRS(
    array $aero,
    float $latA,
    float $lonA,
    array $val,
    array $astriWarnings,
    bool $usaEscludiRadicale,
    bool $esclusaFiltro,
    array $motiviEsclusioneFiltro,
    string $condizione,
    bool $scudoBeneficoAttivo,
    $beneficoInI,
    $denaroBeneficioTrovato,
    bool $denaroAlertGiove,
    ?array $punteggioMyAstral = null,
    ?array $livelloDecima = null,
    ?array $livelloAmore = null,
    ?array $livelloLavoro = null,
    ?array $livelloSalute = null,
    ?array $livelloCasa = null
): array {
    return [
    'icao'           => $aero['icao_code'],
    'iata'           => $aero['iata_code'],
    'nome'                   => $aero['nome'],
    'citta'                  => $aero['citta'],
    'nazione'                => $aero['nazione'],
    'tipo'                   => $aero['tipo'] ?? null,
    'popolazione'            => $aero['popolazione'] ?? null,
    'aeroporto_associato'    => $aero['aeroporto_associato'] ?? null,
    'origine_punto'          => $aero['origine_punto'] ?? 'aeroporto',
    'lat'                    => $latA,
    'lon'            => $lonA,
    'stelline'       => $val['stelline'],
    'stelle_str'     => $val['stelle_str'],
    'val'            => $val['val'],
    'valido'         => $val['is_valida'],
    'veti'           => $val['veti'],
    'astri_warnings' => $astriWarnings,
    'passed_rule_map'=> $usaEscludiRadicale,
    'esclusa_filtro'     => $esclusaFiltro,
    'motivi_esclusione'  => $motiviEsclusioneFiltro,
    'passed_amore'       => ($condizione === 'Amore'),
    'passed_casa'        => ($condizione === 'Casa'),
    'scudo_benefico'     => ($condizione === 'Salute') ? $scudoBeneficoAttivo : null,
    'benefico_in_i'      => ($condizione === 'Salute' && $scudoBeneficoAttivo) ? $beneficoInI : null,
    'passed_denaro'      => ($condizione === 'Denaro'),
    'denaro_beneficio'   => ($condizione === 'Denaro') ? $denaroBeneficioTrovato : null,
    'denaro_alert_giove' => ($condizione === 'Denaro') ? $denaroAlertGiove : null,
    'passed_denaro_low'  => ($condizione === 'Denaro Low'),
    // Punteggio "Discepolo parziale" (roadmap MyAstral, RuleEngineExtended.php).
    // null se MYASTRAL_ALIGNMENT_MODE è disattivo o la condizione non è ancora
    // supportata dal punteggio parziale — non sostituisce mai 'stelline'.
    'punteggio_myastral' => $punteggioMyAstral,
    // Livello 1-7 (UX-0015) per la condizione Decima. null per tutte
    // le altre condizioni o se MYASTRAL_ALIGNMENT_MODE è disattivo.
    'livello_decima'     => $livelloDecima,
    // Livello 1-7 (UX-0016) per la condizione Amore. null per tutte
    // le altre condizioni o se MYASTRAL_ALIGNMENT_MODE è disattivo.
    'livello_amore'      => $livelloAmore,
    // Livello 1-7 (UX-0019) per la condizione Lavoro. null per tutte
    // le altre condizioni o se MYASTRAL_ALIGNMENT_MODE è disattivo.
    'livello_lavoro'     => $livelloLavoro,
    // Livello 1-9 (UX-0020) per la condizione Salute. null per tutte
    // le altre condizioni o se MYASTRAL_ALIGNMENT_MODE è disattivo.
    'livello_salute'     => $livelloSalute,
    // Livello 1-6 (UX-0021) per la condizione Casa. null per tutte
    // le altre condizioni o se MYASTRAL_ALIGNMENT_MODE è disattivo.
    'livello_casa'       => $livelloCasa,
             ];
}

