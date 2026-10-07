<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

final class ImageUrl
{
    public static function resolve(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }

    /**
     * @param  array<int, string|null>  $values
     * @return array<int, string>
     */
    public static function resolveMany(array $values): array
    {
        return array_values(array_filter(array_map(
            fn (?string $value): ?string => self::resolve($value),
            $values,
        )));
    }
}
