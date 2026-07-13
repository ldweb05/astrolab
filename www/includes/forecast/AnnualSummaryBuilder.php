<?php
declare(strict_types=1);

final class AnnualSummaryBuilder
{
    public function build(array $profiles): array
    {
        if ($profiles === []) {
            return [
                'dominant_theme' => null,
                'primary_themes' => [],
                'attention_themes' => [],
                'support_themes' => [],
                'executive_summary' => [
                    'dominant_theme' => null,
                    'top_strengths' => [],
                    'top_attention' => [],
                    'overall_tone' => 'neutral',
                    'confidence' => 0.0,
                ],
                'meta' => [
                    'theme_count' => 0,
                    'generated_from' => 'theme_profiles',
                ],
            ];
        }

        $dominantTheme = array_key_first($profiles);

        $primary = [];
        $attention = [];
        $support = [];

        foreach ($profiles as $theme => $profile) {
            $rank = (int)($profile['rank'] ?? 0);
            $polarity = (string)($profile['polarity'] ?? 'mixed');

            if ($rank > 0 && $rank <= 5) {
                $primary[] = (string)$theme;
            }

            if ($polarity === 'critical') {
                $attention[] = (string)$theme;
            }

            if ($polarity === 'positive') {
                $support[] = (string)$theme;
            }
        }

        $executiveSummary = [
            'dominant_theme' => $dominantTheme,
            'top_strengths' => array_slice($support, 0, 3),
            'top_attention' => array_slice($attention, 0, 3),
            'overall_tone' => $attention !== [] ? 'mixed' : 'positive',
            'confidence' => round(
                array_sum(array_map(
                    static fn(array $profile): float =>
                        (float)($profile['confidence'] ?? 0),
                    $profiles
                )) / max(count($profiles), 1),
                2
            ),
        ];

        return [
            'dominant_theme' => $dominantTheme,
            'primary_themes' => $primary,
            'attention_themes' => $attention,
            'support_themes' => $support,
            'executive_summary' => $executiveSummary,
            'meta' => [
                'theme_count' => count($profiles),
                'generated_from' => 'theme_profiles'
            ],
        ];
    }
}
