<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ImportResultDTO;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class ProductImportService
{
    /**
     * Run the ZIP bulk product import.
     *
     * @param  string  $zipPath
     * @param  (callable(string): void)|null  $progressCallback
     * @return ImportResultDTO
     *
     * @throws InvalidArgumentException
     */
    public function import(string $zipPath, ?callable $progressCallback = null): ImportResultDTO
    {
        $result = new ImportResultDTO();

        // Step 1: Validate ZIP exists
        if (! File::exists($zipPath)) {
            throw new InvalidArgumentException("ZIP file not found at path: {$zipPath}");
        }

        $tempDir = storage_path('app/temp/import-'.time().'-'.Str::random(6));

        try {
            // Step 2: Extract ZIP into temporary directory
            $this->notifyProgress($progressCallback, 'Extracting ZIP file...');
            $extractedPath = $this->extractZip($zipPath, $tempDir);

            // Step 3: Validate ZIP structure
            $this->notifyProgress($progressCallback, 'Validating ZIP structure...');
            $validPath = $this->validateStructure($extractedPath);

            // Step 4: Read products.xlsx using Laravel Excel
            $this->notifyProgress($progressCallback, 'Reading Excel...');
            $excelFile = $this->findExcelFile($validPath);
            if (! $excelFile) {
                throw new InvalidArgumentException('Invalid ZIP structure: Required file products.xlsx is missing.');
            }

            $rows = $this->readExcel($excelFile);
            $this->notifyProgress($progressCallback, "Found {$rows->count()} rows in Excel sheet.");

            $imagesPath = $this->findDirCaseInsensitive($validPath, 'images');
            $imagesDir = $imagesPath ?? $validPath;

            // Step 5 - 10: Process each row
            foreach ($rows as $index => $row) {
                // Skip completely empty rows
                $hasContent = $row->contains(fn (mixed $val): bool => $val !== null && trim((string) $val) !== '');
                if (! $hasContent) {
                    continue;
                }

                $rowNumber = $index + 2; // Row 1 is header row in Excel

                $this->notifyProgress($progressCallback, "Processing row {$index}...");

                try {
                    $this->processRow($row, $rowNumber, $imagesDir, $result);
                } catch (Exception $e) {
                    $result->incrementRowsFailed();
                    $result->addError("Row {$rowNumber}: Unexpected error - {$e->getMessage()}");
                    Log::error("Product import error on row {$rowNumber}", [
                        'exception' => $e,
                        'row' => $row->toArray(),
                    ]);
                }
            }
        } finally {
            // Step 13: Clean up temporary directory
            $this->notifyProgress($progressCallback, 'Cleaning up temporary files...');
            $this->cleanup($tempDir);
        }

        return $result;
    }

    /**
     * Step 2: Extract ZIP archive to target directory.
     */
    public function extractZip(string $zipPath, string $targetDir): string
    {
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $zip = new ZipArchive();
        $status = $zip->open($zipPath);

        if ($status !== true) {
            throw new InvalidArgumentException("Unable to open ZIP archive. Error code: {$status}");
        }

        $zip->extractTo($targetDir);
        $zip->close();

        return $targetDir;
    }

    /**
     * Step 3: Validate presence of products.xlsx and images/ directory.
     */
    public function validateStructure(string $extractedPath): string
    {
        $targetPath = $extractedPath;

        // If ZIP contains a single wrapper directory, dive into it
        $excelPath = $this->findExcelFile($targetPath);

        if (! $excelPath) {
            $subDirs = File::directories($extractedPath);
            if (count($subDirs) === 1) {
                $targetPath = $subDirs[0];
                $excelPath = $this->findExcelFile($targetPath);
            }
        }

        if (! $excelPath) {
            throw new InvalidArgumentException('Invalid ZIP structure: Required file products.xlsx is missing.');
        }

        return $targetPath;
    }

    /**
     * Step 4: Read products.xlsx using Maatwebsite Excel.
     *
     * @return Collection<int, Collection<string, mixed>>
     */
    public function readExcel(string $excelPath): Collection
    {
        $import = new ProductsImport();
        Excel::import($import, $excelPath);

        return $import->getRows();
    }

    /**
     * Process an individual row from Excel.
     *
     * @param  Collection<string, mixed>  $row
     */
    protected function processRow(Collection $row, int $rowNumber, string $imagesDir, ImportResultDTO $result): void
    {
        // Skip completely empty rows
        $hasContent = $row->contains(fn (mixed $val): bool => $val !== null && trim((string) $val) !== '');
        if (! $hasContent) {
            return;
        }

        $data = $row->toArray();

        // Normalize array keys to lowercase
        $normalizedData = [];
        foreach ($data as $key => $value) {
            $normalizedData[strtolower(trim((string) $key))] = $value;
        }

        $name = trim((string) ($normalizedData['name'] ?? $normalizedData['title'] ?? ''));
        $categoryName = trim((string) ($normalizedData['category'] ?? ''));
        $rawPrice = $normalizedData['price'] ?? null;
        $rawComparePrice = $normalizedData['compare_price'] ?? null;
        $rawStockQty = $normalizedData['stock_quantity'] ?? $normalizedData['stock_qty'] ?? $normalizedData['stock'] ?? 0;
        $rawStatus = $normalizedData['status'] ?? 'Active';

        $description = isset($normalizedData['description']) ? trim((string) $normalizedData['description']) : null;
        $rawImages = $normalizedData['multiple_images_names'] ?? $normalizedData['images'] ?? $normalizedData['image'] ?? null;
        $rawVariants = $normalizedData['variants'] ?? null;
        $sku = trim((string) ($normalizedData['sku'] ?? ''));

        // Step 5: Validate required fields
        $validationErrors = [];
        if ($name === '') {
            $validationErrors[] = "Row {$rowNumber}: Name is required.";
        }

        $parsedPrice = $this->parsePrice($rawPrice);
        if ($parsedPrice === null) {
            $validationErrors[] = "Row {$rowNumber}: Price must be a numeric value.";
        }

        if ($validationErrors !== []) {
            foreach ($validationErrors as $error) {
                $result->addError($error);
            }
            $result->incrementSkippedRows();
            $result->incrementRowsFailed();

            return;
        }

        $price = $parsedPrice;
        $comparePrice = $this->parsePrice($rawComparePrice);
        $stockQuantity = max(0, (int) preg_replace('/[^\d]/', '', (string) $rawStockQty));
        $isActive = strtolower(trim((string) $rawStatus)) !== 'inactive';

        // Auto-generate SKU if not provided in sheet
        if ($sku === '') {
            $sku = Str::upper(Str::slug($name));
        }

        // Step 6: Parse images from multiple_images_names AND variants column
        $imageFilenames = $this->parseImageFilenames($rawImages);
        $variantImages = $this->extractImagesFromVariants($rawVariants);
        $allImageFilenames = collect([...$imageFilenames, ...$variantImages])
            ->unique()
            ->values()
            ->all();

        // Step 7 & 8: Process image files
        $storedImagePaths = [];
        foreach ($allImageFilenames as $filename) {
            $sourceImagePath = $this->findImageInDirectory($imagesDir, $filename);

            if (! $sourceImagePath) {
                $result->incrementMissingImages();
                $result->addError("Row {$rowNumber}: Image {$filename} not found.");
                continue;
            }

            $storedPath = $this->storeImage($sourceImagePath, $filename);
            if ($storedPath) {
                $storedImagePaths[] = $storedPath;
            }
        }

        // Handle Category lookup or creation
        $categoryId = null;
        if ($categoryName !== '') {
            $category = Category::query()->firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName), 'is_active' => true]
            );
            $categoryId = $category->id;
        }

        // Step 9 & 10: Create or Update Product in DB Transaction
        DB::transaction(function () use ($name, $sku, $categoryId, $price, $comparePrice, $stockQuantity, $isActive, $description, $storedImagePaths, &$result) {
            $this->createProduct([
                'name' => $name,
                'sku' => $sku,
                'category_id' => $categoryId,
                'price' => $price,
                'compare_price' => $comparePrice,
                'stock_quantity' => $stockQuantity,
                'is_active' => $isActive,
                'description' => $description,
            ], $storedImagePaths);

            $result->incrementProductsImported();
        });
    }

    /**
     * Parse price string into float (handles "$25.99", "25.99", "$1,200.00", "N/A").
     */
    public function parsePrice(mixed $price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }

        $str = trim((string) $price);
        if (strtoupper($str) === 'N/A') {
            return null;
        }

        // Remove currency symbols except digits and decimal point
        $clean = preg_replace('/[^\d.]/', '', $str);
        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    /**
     * Step 6: Split comma-separated image filenames, trim whitespace, ignore empty values.
     *
     * @return array<int, string>
     */
    public function parseImageFilenames(mixed $rawImages): array
    {
        if (blank($rawImages)) {
            return [];
        }

        $imagesString = is_array($rawImages) ? implode(',', $rawImages) : (string) $rawImages;

        return collect(explode(',', $imagesString))
            ->map(fn (string $img) => trim($img))
            ->filter(fn (string $img) => $img !== '')
            ->values()
            ->all();
    }

    /**
     * Extract image filenames from multi-line variant string:
     * e.g. "name: Red, price: $25.99, compare_price: $19.99, stock_qty: 75, image_name: wireless_mouse_red.jpg"
     *
     * @return array<int, string>
     */
    public function extractImagesFromVariants(mixed $rawVariants): array
    {
        if (blank($rawVariants)) {
            return [];
        }

        $images = [];
        $lines = explode("\n", str_replace("\r", "", (string) $rawVariants));

        foreach ($lines as $line) {
            $parts = explode(',', $line);
            foreach ($parts as $part) {
                if (str_contains($part, ':')) {
                    [$key, $val] = explode(':', $part, 2);
                    $key = strtolower(trim($key));
                    $val = trim($val);
                    if ($key === 'image_name' && $val !== '' && strtolower($val) !== 'n/a') {
                        $images[] = $val;
                    }
                }
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * Step 8: Store image file in storage/app/public/products/YYYY/MM/uuid-filename.jpg
     */
    public function storeImage(string $sourcePath, string $filename): ?string
    {
        if (! File::exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $cleanFilename = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        if ($extension !== '') {
            $cleanFilename .= '.'.$extension;
        }

        $year = date('Y');
        $month = date('m');
        $uuid = (string) Str::uuid();
        $storedFilename = "{$uuid}-{$cleanFilename}";

        $directory = "products/{$year}/{$month}";
        $relativePath = "{$directory}/{$storedFilename}";

        // Ensure public storage disk directory exists
        Storage::disk('public')->makeDirectory($directory);

        $contents = File::get($sourcePath);
        Storage::disk('public')->put($relativePath, $contents);

        return $relativePath;
    }

    /**
     * Step 9 & 10: Create Product record using Product model schema.
     *
     * @param  array{name: string, sku: string, category_id: ?int, price: float, compare_price: ?float, stock_quantity: int, is_active: bool, description: ?string}  $data
     * @param  array<int, string>  $storedImagePaths
     */
    public function createProduct(array $data, array $storedImagePaths): Product
    {
        $primaryImage = $storedImagePaths[0] ?? null;

        $galleryImageFiles = collect($storedImagePaths)->map(fn (string $path) => [
            'url' => $path,
            'file_id' => null,
            'cloudinary_public_id' => null,
            'is_uploaded_to_cloudinary' => false,
            'uploaded_image_id' => null,
            'local_path' => $path,
        ])->all();

        $slug = Str::slug($data['name']);
        $existingSlugCount = Product::query()->where('slug', $slug)->where('sku', '!=', $data['sku'])->count();
        if ($existingSlugCount > 0) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        /** @var Product $product */
        $product = Product::query()->updateOrCreate(
            ['sku' => $data['sku']],
            [
                'category_id' => $data['category_id'],
                'title' => $data['name'],
                'slug' => $slug,
                'price' => $data['price'],
                'compare_price' => $data['compare_price'],
                'stock_quantity' => $data['stock_quantity'],
                'description' => $data['description'],
                'image' => $primaryImage,
                'gallery_images' => $storedImagePaths !== [] ? $storedImagePaths : null,
                'gallery_image_files' => $galleryImageFiles !== [] ? $galleryImageFiles : null,
                'is_active' => $data['is_active'],
            ]
        );

        return $product;
    }

    /**
     * Step 13: Delete temporary directory.
     */
    public function cleanup(string $tempDir): void
    {
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * Step 12: Generate import report text summary.
     */
    public function generateImportReport(ImportResultDTO $result): string
    {
        $output = [];
        $output[] = '========================';
        $output[] = 'Import Summary';
        $output[] = '';
        $output[] = 'Products Imported:';
        $output[] = (string) $result->productsImported;
        $output[] = '';
        $output[] = 'Rows Failed:';
        $output[] = (string) $result->rowsFailed;
        $output[] = '';
        $output[] = 'Missing Images:';
        $output[] = (string) $result->missingImages;
        $output[] = '';
        $output[] = 'Skipped Rows:';
        $output[] = (string) $result->skippedRows;
        $output[] = '========================';

        if ($result->errors !== []) {
            $output[] = '';
            $output[] = 'Errors & Warnings:';
            foreach ($result->errors as $error) {
                $output[] = " - {$error}";
            }
        }

        return implode("\n", $output);
    }

    /**
     * Helper to notify progress callback if provided.
     */
    protected function notifyProgress(?callable $callback, string $message): void
    {
        if ($callback !== null) {
            $callback($message);
        }
    }

    /**
     * Helper to find products.xlsx, products.csv, or products.xls in directory case-insensitively.
     */
    protected function findExcelFile(string $directory): ?string
    {
        $allowedNames = ['products.xlsx', 'products.csv', 'products.xls'];
        foreach ($allowedNames as $name) {
            $found = $this->findFileCaseInsensitive($directory, $name);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Helper to find file in directory case-insensitively.
     */
    protected function findFileCaseInsensitive(string $directory, string $filename): ?string
    {
        $exactPath = $directory.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($exactPath)) {
            return $exactPath;
        }

        $files = File::files($directory);
        foreach ($files as $file) {
            if (strtolower($file->getFilename()) === strtolower($filename)) {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Helper to find subdirectory case-insensitively.
     */
    protected function findDirCaseInsensitive(string $directory, string $dirname): ?string
    {
        $exactPath = $directory.DIRECTORY_SEPARATOR.$dirname;
        if (File::isDirectory($exactPath)) {
            return $exactPath;
        }

        $directories = File::directories($directory);
        foreach ($directories as $dir) {
            if (strtolower(basename($dir)) === strtolower($dirname)) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Helper to find image file inside images directory (checking recursively or case-insensitively).
     */
    protected function findImageInDirectory(?string $imagesDir, string $filename): ?string
    {
        if ($imagesDir === null || ! File::isDirectory($imagesDir)) {
            return null;
        }

        $exactPath = $imagesDir.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($exactPath)) {
            return $exactPath;
        }

        // Case-insensitive search in images directory
        $allFiles = File::allFiles($imagesDir);
        foreach ($allFiles as $file) {
            if (strtolower($file->getFilename()) === strtolower($filename) || strtolower($file->getRelativePathname()) === strtolower($filename)) {
                return $file->getPathname();
            }
        }

        return null;
    }
}
