<?php
declare(strict_types=1);

require_once __DIR__.'/ThemeNarrativeBuilder.php';
require_once __DIR__.'/ExecutiveSummaryNarrativeBuilder.php';
require_once __DIR__.'/ThemeSummaryNarrativeBuilder.php';
require_once __DIR__.'/AnnualMeaningBuilder.php';
require_once __DIR__.'/CrossDynamicsBuilder.php';
require_once __DIR__.'/OpportunitiesNarrativeBuilder.php';
require_once __DIR__.'/AttentionNarrativeBuilder.php';
require_once __DIR__.'/ConclusionNarrativeBuilder.php';
final class AnnualReportDraftBuilder
{
    private ThemeNarrativeBuilder $themeNarrative;
    private ExecutiveSummaryNarrativeBuilder $executiveSummary;
    private ThemeSummaryNarrativeBuilder $themeSummary;
    private AnnualMeaningBuilder $meaningBuilder;
    private CrossDynamicsBuilder $crossDynamics;
    private OpportunitiesNarrativeBuilder $opportunities;
    private AttentionNarrativeBuilder $attention;
    private ConclusionNarrativeBuilder $conclusion;

    public function __construct()
    {
        $this->themeNarrative = new ThemeNarrativeBuilder();
        $this->executiveSummary = new ExecutiveSummaryNarrativeBuilder();
        $this->themeSummary = new ThemeSummaryNarrativeBuilder();
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

            'theme_summary' => $this->themeSummary->build(
                $summary['primary_themes'] ?? [],
                $profiles
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
}
