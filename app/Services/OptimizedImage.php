<?php

declare(strict_types=1);

namespace App\Services;

final class OptimizedImage
{
    public function __construct(
        public readonly string $path,
        public readonly string $extension,
        public readonly int $width,
        public readonly int $height,
        public readonly int $size,
    ) {}

    public function cleanup(): void
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }
}
