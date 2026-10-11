<?php

namespace App\Support;

final class Campus
{
    public const MAIN = 'Main';
    public const IBED = 'IBED';

    public static function values(): array
    {
        return [self::MAIN, self::IBED];
    }

    public static function normalize(?string $campus): ?string
    {
        $campus = trim((string) $campus);

        return match (strtolower($campus)) {
            'main', 'main campus' => self::MAIN,
            'ibed', 'ibed campus' => self::IBED,
            default => $campus === '' ? null : $campus,
        };
    }
}
