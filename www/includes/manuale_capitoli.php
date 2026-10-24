<?php
/**
 * includes/manuale_capitoli.php — Elenco ufficiale dei capitoli del Manuale d'uso.
 * docs/roadmaps/roadmap_manuale.md, Fase 1.
 *
 * Unica fonte per: indice di manuale.php, menu a tendina, navigazione
 * "precedente / successivo" e reindirizzamenti dalle vecchie pagine help_*.php.
 *
 * Ogni capitolo:
 *   parte    => chiave di $MANUALE_PARTI
 *   titolo   => titolo mostrato all'utente
 *   soggetto => true se leggibile anche dal soggetto entrato con il CODICE
 *
 * Il testo di ogni capitolo sta in includes/manuale/cNN.php (NN = numero a 2 cifre).
 * Il capitolo 25 e' l'Appendice per il soggetto e viene mostrato con l'etichetta "A".
 */

$MANUALE_PARTI = [
    'A' => 'Parte A — Primi passi',
    'B' => 'Parte B — Le carte',
    'C' => 'Parte C — Cercare il luogo giusto',
    'D' => 'Parte D — Dopo la scelta',
    'E' => 'Parte E — Consultazione',
    'S' => 'Appendice',
];

$MANUALE_CAPITOLI = [
    1  => ['parte' => 'A', 'titolo' => "Cos'è AstroLab e per chi è",                          'soggetto' => false],
    2  => ['parte' => 'A', 'titolo' => 'Entrare, account, piani Free e Supporter',             'soggetto' => false],
    3  => ['parte' => 'A', 'titolo' => 'I soggetti',                                           'soggetto' => false],
    4  => ['parte' => 'A', 'titolo' => 'Dare accesso a un soggetto',                           'soggetto' => false],
    5  => ['parte' => 'A', 'titolo' => 'La Dashboard',                                         'soggetto' => false],
    6  => ['parte' => 'B', 'titolo' => 'Tema Natale',                                          'soggetto' => false],
    7  => ['parte' => 'B', 'titolo' => 'Rivoluzione Solare',                                   'soggetto' => false],
    8  => ['parte' => 'B', 'titolo' => 'Rivoluzione Lunare',                                   'soggetto' => false],
    9  => ['parte' => 'B', 'titolo' => 'Rilocazione',                                          'soggetto' => false],
    10 => ['parte' => 'B', 'titolo' => 'Transiti Planetari',                                   'soggetto' => false],
    11 => ['parte' => 'C', 'titolo' => 'Come AstroLab giudica un luogo',                       'soggetto' => false],
    12 => ['parte' => 'C', 'titolo' => 'Ricerca RSM passo passo',                              'soggetto' => false],
    13 => ['parte' => 'C', 'titolo' => 'Le tre modalità di ricerca',                           'soggetto' => false],
    14 => ['parte' => 'C', 'titolo' => 'I filtri della ricerca',                               'soggetto' => false],
    15 => ['parte' => 'C', 'titolo' => 'Ricerca RL mensile',                                   'soggetto' => false],
    16 => ['parte' => 'C', 'titolo' => 'Confrontare le RSM e salvare le sessioni',             'soggetto' => false],
    17 => ['parte' => 'D', 'titolo' => 'Relazione Annuale',                                    'soggetto' => false],
    18 => ['parte' => 'D', 'titolo' => 'Stampa e PDF',                                         'soggetto' => false],
    19 => ['parte' => 'D', 'titolo' => 'Diario RSM',                                           'soggetto' => true],
    20 => ['parte' => 'E', 'titolo' => 'Le 34 Regole',                                         'soggetto' => false],
    21 => ['parte' => 'E', 'titolo' => 'Legenda: simboli, colori e icone',                     'soggetto' => false],
    22 => ['parte' => 'E', 'titolo' => 'Glossario',                                            'soggetto' => false],
    23 => ['parte' => 'E', 'titolo' => 'Convertitore',                                         'soggetto' => false],
    24 => ['parte' => 'E', 'titolo' => 'Domande frequenti e limiti dei piani',                 'soggetto' => false],
    25 => ['parte' => 'S', 'titolo' => 'Guida per il soggetto',                                'soggetto' => true],
];

/** Etichetta visibile del capitolo: "1".."24", "A" per l'Appendice. */
function manuale_etichetta(int $n): string
{
    return $n === 25 ? 'A' : (string)$n;
}
