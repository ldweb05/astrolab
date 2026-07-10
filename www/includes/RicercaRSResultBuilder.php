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
    bool $denaroAlertGiove
): array {
    return [
    'icao'           => $aero['icao_code'],
    'iata'           => $aero['iata_code'],
    'nome'           => $aero['nome'],
    'citta'          => $aero['citta'],
    'nazione'        => $aero['nazione'],
    'lat'            => $latA,
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
             ];
}

