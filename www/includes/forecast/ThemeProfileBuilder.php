<?php
declare(strict_types=1);

final class ThemeProfileBuilder
{
    public function build(
        array $scores,
        array $polarities,
        array $normalizedContributions
    ): array {
        $profiles = [];
        $rank = 1;

        foreach ($scores as $theme => $score) {
            $polarity = $polarities[$theme] ?? [];

            $profiles[$theme] = [
                'theme'      => (string)$theme,
                'rank'       => $rank++,
                'score'      => round((float)$score, 2),
                'intensity'  => round(
                    (float)($polarity['intensity'] ?? $score),
                    2
                ),
                'positive'   => round(
                    (float)($polarity['positive'] ?? 0),
                    2
                ),
                'critical'   => round(
                    (float)($polarity['critical'] ?? 0),
                    2
                ),
                'neutral'    => round(
                    (float)($polarity['neutral'] ?? 0),
                    2
                ),
                'balance'    => round(
                    (float)($polarity['balance'] ?? 0),
                    2
                ),
                'polarity'   => (string)($polarity['polarity'] ?? 'mixed'),
                'protection' => round((float)($polarity['positive'] ?? 0),2),
                'exposure'   => round((float)($polarity['critical'] ?? 0),2),
                'confidence' => round(
                    min(
                        100,
                        count($normalizedContributions[$theme] ?? []) * 20
                    ),
                    2
                ),
                'summary'    => '',
                'metadata'   => [],
                'sources'    => $normalizedContributions[$theme] ?? [],
            ];
        }

        return $profiles;
    }
}
