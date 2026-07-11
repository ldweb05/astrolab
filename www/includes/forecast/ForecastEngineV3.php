<?php
declare(strict_types=1);

require_once __DIR__.'/AdvancedThemeAggregator.php';
require_once __DIR__.'/ThemeRating.php';
require_once __DIR__.'/ThemePresenter.php';
require_once __DIR__.'/ThemePolarityEngine.php';
require_once __DIR__.'/ThemeProfileBuilder.php';
require_once __DIR__.'/AnnualSummaryBuilder.php';
require_once __DIR__.'/AnnualReportOutlineBuilder.php';
require_once __DIR__.'/AnnualReportDraftBuilder.php';
require_once __DIR__.'/AnnualReportBuilder.php';
require_once __DIR__.'/NarrativeQualityValidator.php';
require_once __DIR__.'/NarrativeStyleEngine.php';
require_once __DIR__.'/EvidenceFormatter.php';

final class ForecastEngineV3
{
    private AdvancedThemeAggregator $aggregator;
    private ThemePolarityEngine $polarity;
    private ThemeProfileBuilder $profiles;
    private AnnualSummaryBuilder $summary;
    private AnnualReportOutlineBuilder $outline;
    private AnnualReportDraftBuilder $draft;
    private AnnualReportBuilder $reportBuilder;
    private NarrativeQualityValidator $validator;
    private NarrativeStyleEngine $style;
    private EvidenceFormatter $evidenceFormatter;

    public function __construct()
    {
        $this->aggregator = new AdvancedThemeAggregator();
        $this->polarity   = new ThemePolarityEngine();
        $this->profiles   = new ThemeProfileBuilder();
        $this->summary    = new AnnualSummaryBuilder();
        $this->outline    = new AnnualReportOutlineBuilder();
        $this->draft      = new AnnualReportDraftBuilder();
        $this->reportBuilder = new AnnualReportBuilder();
        $this->validator = new NarrativeQualityValidator();
        $this->style = new NarrativeStyleEngine();
        $this->evidenceFormatter = new EvidenceFormatter();
    }

    public function generate(array $temaRS): array
    {
        $result = $this->aggregator->aggregate($temaRS);

        $polarities = $this->polarity->build($result['contributions'] ?? []);

        $profiles = $this->profiles->build(
            $result['scores'] ?? [],
            $polarities,
            $result['normalized_contributions'] ?? []
        );

        $summary = $this->summary->build($profiles);

        $outline = $this->outline->build(
            $summary,
            $profiles
        );

        $report = $this->draft->build(
            $summary,
            $profiles,
            $outline
        );

        $annualReport = $this->reportBuilder->build(
            $summary,
            $outline,
            $report
        );

        $formattedEvidence = $this->evidenceFormatter->build(
            $result['evidences'] ?? []
        );

        $annualReport['evidences'] = $formattedEvidence;

        $evidencesByTheme = [];

        foreach ($formattedEvidence as $evidence) {
            $theme = (string)($evidence['code'] ?? '');

            if ($theme === '' || str_starts_with($theme, 'COMPOSITE_')) {
                continue;
            }

            $evidencesByTheme[$theme][] = $evidence;
        }

        foreach ($formattedEvidence as $evidence) {
            if (
                ($evidence['code'] ?? '') ===
                'COMPOSITE_SUN_JUPITER_SAME_HOUSE'
            ) {
                $evidencesByTheme['carriera'][] = $evidence;
            }
        }

        $annualReport['evidences_by_theme'] = $evidencesByTheme;

        foreach ($annualReport['sections'] as &$section) {
            $sectionId = (string)($section['id'] ?? '');
            $sectionThemes = [];

            if (str_starts_with($sectionId, 'theme_')) {
                $sectionThemes[] = substr($sectionId, 6);
            } elseif (
                $sectionId === 'meaning_of_year'
                || $sectionId === 'conclusion'
            ) {
                $sectionThemes[] = (string)($summary['dominant_theme'] ?? '');
            } elseif ($sectionId === 'cross_dynamics') {
                $sectionThemes = $summary['primary_themes'] ?? [];
            } elseif ($sectionId === 'opportunities') {
                $sectionThemes = $summary['support_themes'] ?? [];
            } elseif ($sectionId === 'attention') {
                $sectionThemes = $summary['attention_themes'] ?? [];
            }

            $sectionEvidences = [];

            foreach ($sectionThemes as $theme) {
                foreach ($evidencesByTheme[(string)$theme] ?? [] as $evidence) {
                    $key = (string)($evidence['code'] ?? '')
                        .'|'
                        .(string)($evidence['text'] ?? '');

                    $sectionEvidences[$key] = $evidence;
                }
            }

            $section['evidences'] = array_values($sectionEvidences);
        }
        unset($section);

        $sectionTraceability = [];

        foreach ($annualReport['sections'] as $section) {
            $sectionId = (string)($section['id'] ?? '');

            if ($sectionId === '') {
                continue;
            }

            $sectionTraceability[$sectionId] = [
                'evidence_count' => count($section['evidences'] ?? []),
                'evidence_ids' => array_values(array_unique(array_filter(
                    array_map(
                        static fn(array $evidence): string =>
                            (string)($evidence['evidence_id'] ?? ''),
                        $section['evidences'] ?? []
                    )
                ))),
                'evidence_codes' => array_values(array_unique(
                    array_map(
                        static fn(array $evidence): string =>
                            (string)($evidence['code'] ?? ''),
                        $section['evidences'] ?? []
                    )
                )),
                'rule_ids' => array_values(array_unique(array_filter(
                    array_map(
                        static fn(array $evidence): string =>
                            (string)($evidence['rule_id'] ?? ''),
                        $section['evidences'] ?? []
                    )
                ))),
            ];
        }

        $annualReport['explainability'] = [
            'total_evidences' => count($formattedEvidence),
            'themes_with_evidences' => count($evidencesByTheme),
            'sections' => $sectionTraceability,
        ];

        $annualReport['sections'] = $this->style->refine(
            $annualReport['sections'] ?? []
        );

        $annualReport['word_count'] = str_word_count(
            implode(
                ' ',
                array_map(
                    static fn(array $section): string =>
                        (string)($section['text'] ?? ''),
                    $annualReport['sections']
                )
            )
        );

        $reportValidation = $this->validator->validate(
            $annualReport
        );

        $paragraphs = [];

        foreach ($result['scores'] as $theme => $score) {

            $paragraphs[] = [
                'theme' => $theme,
                'score' => (int)round($score),
                'rating' => ThemeRating::stars((int)round($score)),
            ];
        }

        return [
            'scores'         => $result['scores'],
            'paragraphs'     => ThemePresenter::present($paragraphs),
            'polarities'     => $polarities,
            'theme_profiles' => $profiles,
            'summary'        => $summary,
            'report_outline' => $outline,
            'report_draft'   => $report,
            'annual_report'      => $annualReport,
            'report_validation'  => $reportValidation,
            'context'        => array_merge(
                $result['context'],
                [
                    'planets' => $temaRS['pianeti'] ?? []
                ]
            ),

            'planet_conditions' => $result['planet_conditions'] ?? [],

            // Contratto interno V3.1 / V4 incrementale
            'contributions'            => $result['contributions'] ?? [],
            'normalized_contributions' => $result['normalized_contributions'] ?? [],
            'evidence_groups'          => $result['evidence_groups'] ?? [],
            'evidences'                => $result['evidences'] ?? [],
            'formatted_evidences'      => $formattedEvidence,
        ];
    }
}
