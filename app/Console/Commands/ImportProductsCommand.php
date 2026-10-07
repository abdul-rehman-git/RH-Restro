<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductImportService;
use Exception;
use Illuminate\Console\Command;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {zipFile : Path to the products ZIP file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products and images from a ZIP file containing products.xlsx and an images directory';

    /**
     * Execute the console command.
     */
    public function handle(ProductImportService $service): int
    {
        $zipFile = (string) $this->argument('zipFile');

        // Resolve path if passed relative to storage_path
        if (! file_exists($zipFile) && file_exists(storage_path($zipFile))) {
            $zipFile = storage_path($zipFile);
        }

        if (! file_exists($zipFile)) {
            $this->error("ZIP file not found: {$zipFile}");

            return self::FAILURE;
        }

        $this->info("Starting product import from: {$zipFile}");
        $this->newLine();

        try {
            $result = $service->import($zipFile, function (string $message): void {
                $this->info($message);
            });

            $this->newLine();
            $this->line($service->generateImportReport($result));

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
