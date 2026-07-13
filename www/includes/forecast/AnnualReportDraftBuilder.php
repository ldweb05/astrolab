<?php
declare(strict_types=1);

require_once __DIR__.'/ThemeNarrativeBuilder.php';
require_once __DIR__.'/ExecutiveSummaryNarrativeBuilder.php';
require_once __DIR__.'/AnnualMeaningBuilder.php';
require_once __DIR__.'/CrossDynamicsBuilder.php';
require_once __DIR__.'/OpportunitiesNarrativeBuilder.php';
require_once __DIR__.'/AttentionNarrativeBuilder.php';
require_once __DIR__.'/ConclusionNarrativeBuilder.php';
final class AnnualReportDraftBuilder
{
    private ThemeNarrativeBuilder $themeNarrative;
    private ExecutiveSummaryNarrativeBuilder $executiveSummary;
    private AnnualMeaningBuilder $meaningBuilder;
    private CrossDynamicsBuilder $crossDynamics;
    private OpportunitiesNarrativeBuilder $opportunities;
    private AttentionNarrativeBuilder $attention;
    private ConclusionNarrativeBuilder $conclusion;

    public function __construct()
    {
        $this->themeNarrative = new ThemeNarrativeBuilder();
        $this->executiveSummary = new ExecutiveSummaryNarrativeBuilder();
        $this->meaningBuilder = new AnnualMeaningBuilder();
        $this->crossDynamics = new CrossDynamicsBuilder();
        $this->opportunities = new OpportunitiesNarrativeBuilder();
        $this->attention = new AttentionNarrativeBuilder();
        $this->conclusion = new ConclusionNarrativeBuilder();
    }

    public function build(
        array $summary,
        array $profiles,
        array $outline
    ): array {
        $sections = [];

        foreach ($outline as $section) {
            $id = (string)($section['id'] ?? '');

            $sections[] = [
                'id'    => $id,
                'title' => (string)($section['title'] ?? ''),
                'text'  => $this->textFor(
                    $id,
                    $section,
                    $summary,
                    $profiles
                ),
            ];
        }

        return $sections;
    }

    private function textFor(
        string $id,
        array $section,
        array $summary,
        array $profiles
    ): string {
        return match ($id) {
            'executive_summary' => $this->executiveSummary->build(
                $summary['executive_summary'] ?? []
            ),

            'meaning_of_year' => $this->meaningBuilder->build(
                (string)($summary['dominant_theme'] ?? ''),
                $profiles[(string)($summary['dominant_theme'] ?? '')] ?? []
            ),

            'cross_dynamics' => $this->crossDynamics->build(
                $summary['primary_themes'] ?? [],
                $profiles
            ),

            'opportunities' => $this->opportunities->build(
                $summary['support_themes'] ?? [],
                $profiles
            ),

            'attention' => $this->attention->build(
                $summary['attention_themes'] ?? [],
                $profiles
            ),

            'conclusion' => $this->conclusion->build(
                (string)($summary['dominant_theme'] ?? ''),
                $summary,
                $profiles
            ),

            default => str_starts_with($id, 'theme_')
                ? $this->themeNarrative->build(
                    (string)($section['theme'] ?? ''),
                    $profiles[(string)($section['theme'] ?? '')] ?? []
                )
                : '',
        };
    }

    private function meaningOfYear(string $theme, array $profiles): string
    {
        if ($theme === '' || !isset($profiles[$theme])) {
            return '';
        }

        return sprintf(
            "Questa Rivoluzione Solare sembrerebbe porre in primo piano il tema %s. "
            ."Nel corso dell'anno tale ambito potrebbe assumere un ruolo centrale, "
            ."richiedendo attenzione, partecipazione attiva e capacità di adattamento.",
            $this->label($theme)
        );
    }

    private function themeText(string $theme, array $profiles): string
    {
        if ($theme === '' || !isset($profiles[$theme])) {
            return '';
        }

        $profile = $profiles[$theme];
        $polarity = (string)($profile['polarity'] ?? 'mixed');

        $ending = match ($polarity) {
            'positive' =>
                "Le configurazioni presenti sembrerebbero offrire risorse utili "
                ."per affrontare questo ambito con maggiore efficacia.",

            'critical' =>
                "Questo settore potrebbe richiedere maggiore prudenza, continuità "
                ."e capacità di intervenire tempestivamente qualora fosse necessario.",

            default =>
                "L'andamento di questo settore potrebbe dipendere soprattutto "
                ."dal modo in cui verranno gestite le circostanze concrete.",
        };

        return sprintf(
            "L'area %s potrebbe rappresentare uno dei temi significativi dell'anno. %s",
            $this->label($theme),
            $ending
        );
    }

    private function crossDynamics(array $themes): string
    {
        if ($themes === []) {
            return '';
        }

        $labels = array_map(
            fn(string $theme): string => $this->label($theme),
            array_slice($themes, 0, 3)
        );

        return sprintf(
            "Le dinamiche relative a %s potrebbero influenzarsi reciprocamente. "
            ."Sarebbe quindi utile osservare l'anno come un percorso unitario, "
            ."evitando di considerare ogni area della vita in modo isolato.",
            implode(', ', $labels)
        );
    }

    private function opportunities(array $themes): string
    {
        if ($themes === []) {
            return "Le opportunità dell'anno potrebbero emergere soprattutto "
                ."attraverso una gestione consapevole delle circostanze.";
        }

        return sprintf(
            "Le configurazioni più favorevoli sembrerebbero concentrarsi soprattutto "
            ."negli ambiti legati a %s. Tali risorse non garantirebbero l'assenza "
            ."di difficoltà, ma potrebbero facilitarne la gestione.",
            implode(', ', array_map(
                fn(string $theme): string => $this->label($theme),
                array_slice($themes, 0, 3)
            ))
        );
    }

    private function attention(array $themes): string
    {
        if ($themes === []) {
            return "Non emergerebbero aree nettamente critiche, ma resterebbe "
                ."comunque utile mantenere attenzione e capacità di adattamento.";
        }

        return sprintf(
            "Gli ambiti relativi a %s potrebbero richiedere maggiore attenzione. "
            ."Un atteggiamento prudente e tempestivo potrebbe aiutare a contenere "
            ."eventuali difficoltà.",
            implode(', ', array_map(
                fn(string $theme): string => $this->label($theme),
                array_slice($themes, 0, 3)
            ))
        );
    }

    private function conclusion(string $theme): string
    {
        if ($theme === '') {
            return '';
        }

        return sprintf(
            "Nel complesso, l'anno sembrerebbe svilupparsi intorno al tema %s. "
            ."Le possibilità offerte dalla Rivoluzione Solare potrebbero essere "
            ."valorizzate attraverso consapevolezza, flessibilità e partecipazione attiva.",
            $this->label($theme)
        );
    }

    private function label(string $theme): string
    {
        return match ($theme) {
            'carriera' => 'della carriera e della realizzazione personale',
            'lavoro' => 'del lavoro e dell’organizzazione quotidiana',
            'amore' => 'delle relazioni e della vita affettiva',
            'salute' => 'del benessere e della gestione delle energie',
            'famiglia' => 'della famiglia e della vita privata',
            'casa' => 'della casa e della stabilità personale',
            'studio' => 'dello studio e della crescita personale',
            'viaggi' => 'dei viaggi e delle nuove prospettive',
            'amicizie' => 'delle amicizie e della vita sociale',
            'figli' => 'dei figli e delle responsabilità affettive',
            'creativita' => 'della creatività e dell’espressione personale',
            'spiritualita' => 'dell’interiorità e della ricerca personale',
            'trasformazione' => 'del cambiamento e della trasformazione',
            'prove' => 'delle responsabilità e delle prove',
            default => 'di '.$theme,
        };
    }
}
