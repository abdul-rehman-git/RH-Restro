<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\ImageUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

class ImageOptimizer
{
    public function optimize(UploadedFile $file, string $field = 'image'): OptimizedImage
    {
        $sourcePath = $file->getRealPath();

        if (! is_string($sourcePath) || $sourcePath === '' || ! is_file($sourcePath)) {
            $this->fail($field, 'The uploaded image could not be read.');
        }

        try {
            $image = $this->manager()->read($sourcePath)->orient();
        } catch (\Throwable) {
            $this->fail($field, 'The uploaded file is not a supported image.');
        }

        $width = $image->width();
        $height = $image->height();

        if ($width <= 0 || $height <= 0) {
            $this->fail($field, 'The uploaded image has invalid dimensions.');
        }

        if (($width * $height) > ImageUpload::maxPixels()) {
            $this->fail($field, 'The uploaded image is too large to process safely.');
        }

        $image = $image->scaleDown(ImageUpload::maxWidth(), ImageUpload::maxHeight());
        $targetWidth = $image->width();
        $targetHeight = $image->height();

        try {
            [$extension, $encoded] = $this->encode($image, $file);
            $tempPath = $this->temporaryPath($extension);
            $encoded->save($tempPath);

            clearstatcache(true, $tempPath);
            $size = is_file($tempPath) ? (filesize($tempPath) ?: $encoded->size()) : 0;

            if ($size <= 0) {
                throw new RuntimeException('Optimized image file is empty.');
            }

            return new OptimizedImage(
                path: $tempPath,
                extension: $extension,
                width: $targetWidth,
                height: $targetHeight,
                size: $size,
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            if (isset($tempPath) && is_file($tempPath)) {
                @unlink($tempPath);
            }

            $this->fail($field, 'The uploaded image could not be optimized. Please try a different image.');
        }
    }

    /**
     * @return array{0: string, 1: EncodedImageInterface}
     */
    private function encode(ImageInterface $image, UploadedFile $file): array
    {
        $sourceExtension = $this->sourceExtension($file);
        $preserveAlpha = in_array($sourceExtension, ['png', 'webp'], true);
        $manager = $this->manager();

        if (ImageUpload::preferWebp() && $manager->driver()->supports('webp')) {
            return [
                'webp',
                $image->toWebp(ImageUpload::webpQuality($preserveAlpha), true),
            ];
        }

        if ($preserveAlpha) {
            return ['png', $image->toPng()];
        }

        return [
            'jpg',
            $image->toJpeg(ImageUpload::jpegQuality(), true, true),
        ];
    }

    private function manager(): ImageManager
    {
        return extension_loaded('imagick')
            ? ImageManager::imagick()
            : ImageManager::gd();
    }

    private function sourceExtension(UploadedFile $file): string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());

        return match ($extension) {
            'jpeg' => 'jpg',
            default => $extension,
        };
    }

    private function temporaryPath(string $extension): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to create a temporary image file.');
        }

        $optimizedPath = "{$tempPath}.{$extension}";

        if (! @rename($tempPath, $optimizedPath)) {
            @unlink($tempPath);

            throw new RuntimeException('Unable to prepare a temporary image path.');
        }

        return $optimizedPath;
    }

    /**
     * @return never
     */
    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
