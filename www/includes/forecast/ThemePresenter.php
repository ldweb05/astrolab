<?php
declare(strict_types=1);

require_once __DIR__ . '/../atlas/ThemeCatalog.php';

final class ThemePresenter
{
    public static function present(array $paragraphs): array
    {
        $out = [];

        foreach ($paragraphs as $item) {
            $theme = (string)($item['theme'] ?? '');

            $meta = ThemeCatalog::THEMES[$theme] ?? [
                'label' => ucfirst(str_replace('_', ' ', $theme)),
                'icon'  => '•',
            ];

            $out[] = array_merge($item, [
                'label' => $meta['label'],
                'icon'  => $meta['icon'],
            ]);
        }

        return $out;
    }
}
