<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\SettingStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CloudinaryStorageService
{
    private string $cloudName;

    private string $apiKey;

    private string $apiSecret;

    private string $folder;

    public function __construct()
    {
        $settings = SettingStore::cloudinary();

        $this->cloudName = trim((string) ($settings['cloud_name'] ?? ''));
        $this->apiKey = trim((string) ($settings['api_key'] ?? ''));
        $this->apiSecret = trim((string) ($settings['api_secret'] ?? ''));
        $this->folder = trim((string) ($settings['folder'] ?? 'rh-commerce'));
    }

    /**
     * @return array{id: string, url: string}
     */
    public function uploadPublicFile(string $filePath, string $fileName, ?string $publicId = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Cloudinary is not configured. Add credentials in Settings.');
        }

        if (! is_file($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }

        $timestamp = time();
        $resolvedPublicId = $publicId ?: pathinfo($fileName, PATHINFO_FILENAME);
        $signature = $this->buildUploadSignature($timestamp, $resolvedPublicId);
        $endpoint = sprintf('https://api.cloudinary.com/v1_1/%s/image/upload', $this->cloudName);

        $response = Http::timeout(120)
            ->asMultipart()
            ->attach('file', fopen($filePath, 'r'), $fileName)
            ->post($endpoint, [
                ['name' => 'api_key', 'contents' => $this->apiKey],
                ['name' => 'timestamp', 'contents' => (string) $timestamp],
                ['name' => 'signature', 'contents' => $signature],
                ['name' => 'folder', 'contents' => $this->folder],
                ['name' => 'public_id', 'contents' => $resolvedPublicId],
                ['name' => 'overwrite', 'contents' => 'true'],
            ]);

        if (! $response->successful()) {
            $body = $response->body();
            $message = (string) data_get($response->json(), 'error.message', $body);

            Log::error('Cloudinary upload failed', [
                'status' => $response->status(),
                'body' => $body,
                'cloud_name' => $this->cloudName,
                'api_key' => $this->apiKey,
                'folder' => $this->folder,
            ]);

            if (str_contains($message, 'missing permissions') || str_contains($message, 'actions=["create"]')) {
                throw new RuntimeException(
                    'Cloudinary API key is missing upload (create) permission. '.
                    'In Cloudinary Console → Settings → API Keys, use an unrestricted key or enable Upload/Create on this key.'
                );
            }

            throw new RuntimeException('Cloudinary upload failed: HTTP '.$response->status().' - '.$message);
        }

        $data = $response->json();

        if (! is_array($data) || empty($data['public_id']) || empty($data['secure_url'])) {
            throw new RuntimeException('Cloudinary upload failed: invalid response payload.');
        }

        Log::info('File uploaded to Cloudinary', [
            'public_id' => $data['public_id'],
            'secure_url' => $data['secure_url'],
        ]);

        return [
            'id' => (string) $data['public_id'],
            'url' => (string) $data['secure_url'],
        ];
    }

    public function deleteFile(?string $publicId): void
    {
        $publicId = trim((string) $publicId);

        if (! $this->isConfigured() || $publicId === '') {
            return;
        }

        $timestamp = time();
        $signature = $this->buildDestroySignature($publicId, $timestamp);
        $endpoint = sprintf('https://api.cloudinary.com/v1_1/%s/image/destroy', $this->cloudName);

        $response = Http::asForm()->post($endpoint, [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'api_key' => $this->apiKey,
            'signature' => $signature,
            'invalidate' => true,
        ]);

        if ($response->successful() && ($response->json('result') === 'ok')) {
            Log::info('File deleted from Cloudinary', ['public_id' => $publicId]);

            return;
        }

        Log::warning('Cloudinary delete failed', [
            'public_id' => $publicId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->cloudName !== ''
            && $this->apiKey !== ''
            && $this->apiSecret !== '';
    }

    private function buildUploadSignature(int $timestamp, string $publicId): string
    {
        return $this->signParams([
            'folder' => $this->folder,
            'overwrite' => 'true',
            'public_id' => $publicId,
            'timestamp' => (string) $timestamp,
        ]);
    }

    private function buildDestroySignature(string $publicId, int $timestamp): string
    {
        return $this->signParams([
            'public_id' => $publicId,
            'timestamp' => (string) $timestamp,
        ]);
    }

    /**
     * @param  array<string, string>  $params
     */
    private function signParams(array $params): string
    {
        $filtered = array_filter($params, static fn ($value) => $value !== null && $value !== '');
        ksort($filtered);

        $pairs = [];
        foreach ($filtered as $key => $value) {
            $pairs[] = $key.'='.$value;
        }

        return sha1(implode('&', $pairs).$this->apiSecret);
    }
}
