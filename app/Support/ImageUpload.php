<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rules\File as FileRule;

class ImageUpload
{
    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        /** @var array<int, string> $extensions */
        $extensions = config('uploads.images.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']);

        return $extensions;
    }

    public static function maxKilobytes(): int
    {
        return (int) config('uploads.images.max_upload_kb', 5120);
    }

    public static function reviewMaxKilobytes(): int
    {
        return (int) config('uploads.images.review_max_upload_kb', 3072);
    }

    public static function maxWidth(): int
    {
        return (int) config('uploads.images.max_width', 1600);
    }

    public static function maxHeight(): int
    {
        return (int) config('uploads.images.max_height', 1600);
    }

    public static function maxPixels(): int
    {
        return (int) config('uploads.images.max_pixels', 40_000_000);
    }

    public static function preferWebp(): bool
    {
        return (bool) config('uploads.images.prefer_webp', true);
    }

    public static function webpQuality(bool $preserveAlpha = false): int
    {
        return (int) config(
            $preserveAlpha ? 'uploads.images.webp_alpha_quality' : 'uploads.images.webp_quality',
            $preserveAlpha ? 88 : 82,
        );
    }

    public static function jpegQuality(): int
    {
        return (int) config('uploads.images.jpeg_quality', 82);
    }

    public static function pngCompression(): int
    {
        return (int) config('uploads.images.png_compression', 8);
    }

    public static function validationRule(?int $maxKilobytes = null): FileRule
    {
        return FileRule::image()
            ->types(self::allowedExtensions())
            ->max($maxKilobytes ?? self::maxKilobytes());
    }
}
