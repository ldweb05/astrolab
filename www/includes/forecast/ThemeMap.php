<?php
declare(strict_types=1);

final class ThemeMap
{
    private const MAP = [
        'successo'          => 'carriera',
        'prestigio'         => 'carriera',
        'potere'            => 'carriera',
        'innovazione'       => 'carriera',
        'popolarita'        => 'carriera',
        'immagine_pubblica' => 'carriera',
        'realizzazione'     => 'carriera',

        'affari'            => 'denaro',
        'entrate'           => 'denaro',
        'spese'             => 'denaro',
        'patrimonio'        => 'denaro',
        'eredita'           => 'denaro',
        'sicurezza'         => 'denaro',

        'matrimonio'        => 'relazioni',
        'societa'           => 'relazioni',
        'contratti'         => 'relazioni',
        'separazioni'       => 'relazioni',
        'rotture'           => 'relazioni',
        'idealizzazione'    => 'relazioni',

        'stress'            => 'salute',
        'fatica'            => 'salute',
        'incidenti'         => 'salute',
        'energia'           => 'salute',

        'responsabilita'    => 'lavoro',
        'dovere'            => 'lavoro',
        'restrizioni'       => 'lavoro',

        'traslochi'         => 'casa',
        'immobili'          => 'casa',

        'estero'            => 'viaggi',
        'viaggi_brevi'      => 'viaggi',
        'spostamenti'       => 'viaggi',

        'comunicazione'     => 'studio',
        'parenti'           => 'famiglia',

        'rigenerazione'     => 'trasformazione',
        'cambiamenti'       => 'trasformazione',
        'liberazione'       => 'trasformazione',
        'psicologia'        => 'trasformazione',

        'isolamento'        => 'prove',
        'nemici'            => 'prove',
        'tensioni'          => 'prove',
        'conflitti'         => 'prove',
        'discussioni'       => 'prove',
        'confusione'        => 'prove',
        'imprevisti'        => 'prove',

        'introspezione'     => 'spiritualita',
        'intuizione'        => 'spiritualita',

        'identita'          => 'crescita_personale',
        'iniziative'        => 'crescita_personale',
    ];

    public static function normalize(string $theme): string
    {
        return self::MAP[$theme] ?? $theme;
    }
}
