<?php

declare(strict_types=1);

return [
    'images' => [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_upload_kb' => 5120,
        'review_max_upload_kb' => 3072,
        'max_width' => 1600,
        'max_height' => 1600,
        'max_pixels' => 40_000_000,
        'prefer_webp' => true,
        'webp_quality' => 82,
        'webp_alpha_quality' => 88,
        'jpeg_quality' => 82,
        'png_compression' => 8,
    ],
];
