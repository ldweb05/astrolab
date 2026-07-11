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

            // Contratto interno V3.1 / V4 incrementale
            'contributions'            => $result['contributions'] ?? [],
            'normalized_contributions' => $result['normalized_contributions'] ?? [],
            'evidence_groups'          => $result['evidence_groups'] ?? [],
            'evidences'                => $result['evidences'] ?? [],
            'formatted_evidences'      => $formattedEvidence,
        ];
    }
}
