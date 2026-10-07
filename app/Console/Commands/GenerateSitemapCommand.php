<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SitemapService;
use Exception;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--force : Overwrite existing sitemap.xml and robots.txt files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate physical sitemap.xml and robots.txt files in the public directory for search engines';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Generating XML Sitemap & robots.txt...');

        try {
            $sitemapResult = $sitemapService->generateAndSave();
            $sitemapService->generateRobotsTxt();

            $this->newLine();
            $this->components->info('Sitemap generated successfully!');
            $this->line("  - <comment>File:</comment> {$sitemapResult['path']}");
            $this->line("  - <comment>Total URLs:</comment> {$sitemapResult['urls_count']}");
            $this->line("  - <comment>Image Tags:</comment> {$sitemapResult['images_count']}");
            $this->line("  - <comment>Size:</comment> " . number_format($sitemapResult['size_bytes']) . " bytes");
            $this->line("  - <comment>Robots File:</comment> " . public_path('robots.txt'));
            $this->newLine();

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Failed to generate sitemap: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}

