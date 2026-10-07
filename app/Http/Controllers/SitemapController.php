<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    public function index(): Response
    {
        $xml = $this->sitemapService->generateXml();

        // Keep physical file in sync on disk if missing or older than 1 hour
        $filePath = public_path('sitemap.xml');
        if (!file_exists($filePath) || (time() - (int) @filemtime($filePath)) > 3600) {
            @file_put_contents($filePath, $xml);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600, s-maxage=3600',
        ]);
    }
}
