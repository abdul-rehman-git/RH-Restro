<?php

namespace Tests;

use Illuminate\Http\UploadedFile;

trait CreatesTestImages
{
    /**
     * @var array<int, string>
     */
    protected array $generatedImagePaths = [];

    protected function makePatternedJpegUpload(
        string $filename = 'large-photo.jpg',
        int $width = 2400,
        int $height = 1800,
    ): UploadedFile {
        $image = imagecreatetruecolor($width, $height);
        $this->assertNotFalse($image);

        for ($y = 0; $y < $height; $y += 40) {
            $color = imagecolorallocate(
                $image,
                ($y * 5) % 255,
                ($y * 9) % 255,
                ($y * 13) % 255,
            );

            imagefilledrectangle($image, 0, $y, $width, min($height, $y + 40), $color);
        }

        for ($x = 0; $x < $width; $x += 60) {
            $color = imagecolorallocate(
                $image,
                ($x * 7) % 255,
                ($x * 11) % 255,
                ($x * 3) % 255,
            );

            imageline($image, $x, 0, max(0, $width - $x), $height, $color);
        }

        for ($i = 0; $i < 120; $i++) {
            $x1 = ($i * 97) % max(1, $width - 220);
            $y1 = ($i * 61) % max(1, $height - 220);
            $x2 = min($width, $x1 + 120 + ($i % 90));
            $y2 = min($height, $y1 + 120 + (($i * 3) % 90));
            $color = imagecolorallocate(
                $image,
                ($i * 23) % 255,
                ($i * 41) % 255,
                ($i * 59) % 255,
            );

            imagerectangle($image, $x1, $y1, $x2, $y2, $color);
        }

        $path = tempnam(sys_get_temp_dir(), 'jpeg_');
        $this->assertNotFalse($path);
        imagejpeg($image, $path, 96);
        imagedestroy($image);

        return $this->toUploadedFile($path, $filename, 'image/jpeg');
    }

    protected function makeTransparentPngUpload(
        string $filename = 'transparent-art.png',
        int $width = 2200,
        int $height = 2200,
    ): UploadedFile {
        $image = imagecreatetruecolor($width, $height);
        $this->assertNotFalse($image);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);

        for ($i = 0; $i < 60; $i++) {
            $color = imagecolorallocatealpha(
                $image,
                20 + (($i * 17) % 200),
                20 + (($i * 31) % 200),
                20 + (($i * 47) % 200),
                $i % 3 === 0 ? 20 : 0,
            );

            imagefilledellipse(
                $image,
                200 + (($i * 73) % max(220, $width - 200)),
                200 + (($i * 53) % max(220, $height - 200)),
                160 + (($i * 11) % 240),
                160 + (($i * 7) % 240),
                $color,
            );
        }

        $centerShape = imagecolorallocatealpha($image, 230, 120, 60, 0);
        imagefilledrectangle(
            $image,
            (int) ($width * 0.3),
            (int) ($height * 0.3),
            (int) ($width * 0.7),
            (int) ($height * 0.7),
            $centerShape,
        );

        $path = tempnam(sys_get_temp_dir(), 'png_');
        $this->assertNotFalse($path);
        imagepng($image, $path, 1);
        imagedestroy($image);

        return $this->toUploadedFile($path, $filename, 'image/png');
    }

    protected function cleanupGeneratedImages(): void
    {
        foreach ($this->generatedImagePaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->generatedImagePaths = [];
    }

    protected function resolveStoredPath(string $storedPath): string
    {
        if (str_starts_with($storedPath, 'storage/')) {
            return storage_path('app/public/'.substr($storedPath, strlen('storage/')));
        }

        return public_path($storedPath);
    }

    private function toUploadedFile(string $path, string $filename, string $mimeType): UploadedFile
    {
        $this->generatedImagePaths[] = $path;

        return new UploadedFile($path, $filename, $mimeType, null, true);
    }
}
