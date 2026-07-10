<?php

final class AnnualForecastEngine
{
    private const PLANET_NAMES = [
        0 => 'Sole',
        1 => 'Luna',
        2 => 'Mercurio',
        3 => 'Venere',
        4 => 'Marte',
        5 => 'Giove',
        6 => 'Saturno',
        7 => 'Urano',
        8 => 'Nettuno',
        9 => 'Plutone',
    ];

    private const HOUSE_THEMES = [
        1  => ['area' => 'identita_salute', 'label' => 'identità, vitalità e benessere personale'],
        2  => ['area' => 'denaro', 'label' => 'denaro, risorse e sicurezza materiale'],
        3  => ['area' => 'comunicazione', 'label' => 'comunicazione, spostamenti e rapporti vicini'],
        4  => ['area' => 'casa_famiglia', 'label' => 'casa, famiglia e radici'],
        5  => ['area' => 'amore_creativita', 'label' => 'amore, figli, creatività e piaceri'],
        6  => ['area' => 'lavoro_salute', 'label' => 'lavoro quotidiano, abitudini e salute'],
        7  => ['area' => 'relazioni', 'label' => 'relazioni, coppia, soci e contratti'],
        8  => ['area' => 'trasformazioni', 'label' => 'trasformazioni, risorse condivise e prove profonde'],
        9  => ['area' => 'viaggi_studi', 'label' => 'viaggi, studi superiori e apertura mentale'],
        10 => ['area' => 'carriera', 'label' => 'carriera, realizzazione e visibilità'],
        11 => ['area' => 'amicizie_progetti', 'label' => 'amicizie, reti sociali e progetti'],
        12 => ['area' => 'prove_interiori', 'label' => 'prove interiori, isolamento e dimensione nascosta'],
    ];

    public function genera(array $temaRS, array $valutazione): array
    {
        $configurazioni = $this->estraiConfigurazioni($temaRS);
        $temi = $this->aggregaTemi($configurazioni);
        $paragrafi = $this->generaParagrafi($temi);

        return [
            'titolo' => 'Previsione Annuale',
            'introduzione' => $this->generaIntroduzione($valutazione),
            'paragrafi' => $paragrafi,
            'configurazioni' => $configurazioni,
        ];
    }

    private function estraiConfigurazioni(array $temaRS): array
    {
        $risultati = [];

        foreach (($temaRS['pianeti'] ?? []) as $id => $pianeta) {
            $casa = (int)($pianeta['casa'] ?? 0);

            if (!isset(self::PLANET_NAMES[$id], self::HOUSE_THEMES[$casa])) {
                continue;
            }

            $risultati[] = [
                'pianeta_id' => (int)$id,
                'pianeta' => self::PLANET_NAMES[$id],
                'casa' => $casa,
                'area' => self::HOUSE_THEMES[$casa]['area'],
                'tema' => self::HOUSE_THEMES[$casa]['label'],
                'peso' => $this->calcolaPeso((int)$id, $casa),
                'polarita' => $this->calcolaPolarita((int)$id, $casa),
                'fonte' => self::PLANET_NAMES[$id] . ' in ' . $this->numeroRomano($casa) . ' Casa',
            ];
        }

        return $risultati;
    }

    private function aggregaTemi(array $configurazioni): array
    {
        $temi = [];

        foreach ($configurazioni as $configurazione) {
            $area = $configurazione['area'];

            if (!isset($temi[$area])) {
                $temi[$area] = [
                    'area' => $area,
                    'tema' => $configurazione['tema'],
                    'peso' => 0,
                    'positivi' => 0,
                    'critici' => 0,
                    'fonti' => [],
                ];
            }

            $temi[$area]['peso'] += $configurazione['peso'];
            $temi[$area]['fonti'][] = $configurazione['fonte'];

            if ($configurazione['polarita'] === 'positiva') {
                $temi[$area]['positivi']++;
            } elseif ($configurazione['polarita'] === 'critica') {
                $temi[$area]['critici']++;
            }
        }

        uasort($temi, static fn(array $a, array $b): int => $b['peso'] <=> $a['peso']);

        return array_values($temi);
    }

    private function generaParagrafi(array $temi): array
    {
        $paragrafi = [];

        foreach (array_slice($temi, 0, 5) as $tema) {
            $testo = $this->testoTema($tema);

            $paragrafi[] = [
                'area' => $tema['area'],
                'testo' => $testo,
                'fonti' => array_values(array_unique($tema['fonti'])),
                'peso' => $tema['peso'],
            ];
        }

        return $paragrafi;
    }

    private function testoTema(array $tema): string
    {
        $apertura = 'Nel corso dell’anno, ' . $tema['tema'] . ' potrebbe rappresentare uno dei settori più attivi. ';

        if ($tema['positivi'] > $tema['critici']) {
            return $apertura
                . 'La configurazione complessiva può favorire occasioni di crescita, maggiore fluidità e risultati concreti, '
                . 'pur richiedendo partecipazione e scelte consapevoli.';
        }

        if ($tema['critici'] > $tema['positivi']) {
            return $apertura
                . 'Questo settore può richiedere particolare attenzione, capacità di adattamento e una gestione prudente '
                . 'delle situazioni più impegnative.';
        }

        return $apertura
            . 'Potrebbero alternarsi opportunità e momenti più impegnativi, rendendo necessario trovare un equilibrio '
            . 'tra iniziativa, prudenza e capacità di adattamento.';
    }

    private function generaIntroduzione(array $valutazione): string
    {
        if (!empty($valutazione['veti'])) {
            return 'La Rivoluzione Solare presenta configurazioni che richiedono particolare cautela. '
                . 'La previsione seguente descrive i temi più probabili dell’anno senza considerare gli eventi come inevitabili.';
        }

        $stelle = (int)($valutazione['stelline'] ?? 0);

        return match (true) {
            $stelle >= 4 => 'L’anno appare complessivamente ricco di possibilità, con alcuni settori capaci di offrire crescita, sostegno e risultati visibili.',
            $stelle === 3 => 'L’anno appare articolato, con opportunità interessanti affiancate da alcune aree che richiederanno attenzione.',
            default => 'L’anno può presentare diverse prove e richiedere una gestione prudente delle energie, delle scelte e delle priorità.',
        };
    }

    private function calcolaPeso(int $pianetaId, int $casa): int
    {
        $pesoPianeta = [
            0 => 90,
            1 => 55,
            2 => 45,
            3 => 75,
            4 => 85,
            5 => 95,
            6 => 90,
            7 => 75,
            8 => 70,
            9 => 80,
        ];

        $pesoCasa = [
            1 => 20,
            4 => 15,
            5 => 20,
            6 => 25,
            7 => 20,
            10 => 30,
            12 => 25,
        ];

        return ($pesoPianeta[$pianetaId] ?? 50) + ($pesoCasa[$casa] ?? 10);
    }

    private function calcolaPolarita(int $pianetaId, int $casa): string
    {
        if (in_array($pianetaId, [3, 5], true)) {
            return 'positiva';
        }

        if (in_array($pianetaId, [4, 6, 7, 8, 9], true)) {
            return 'critica';
        }

        if ($pianetaId === 0 && in_array($casa, [5, 9, 10, 11], true)) {
            return 'positiva';
        }

        return 'neutra';
    }

    private function numeroRomano(int $numero): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$numero] ?? (string)$numero;
    }
}
