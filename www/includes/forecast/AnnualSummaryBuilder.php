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

        return [
            'dominant_theme' => $dominantTheme,
            'primary_themes' => $primary,
            'attention_themes' => $attention,
            'support_themes' => $support,
            'meta' => [
                'theme_count' => count($profiles),
                'generated_from' => 'theme_profiles'
            ],
        ];
    }
}
