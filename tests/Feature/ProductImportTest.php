<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected string $testStoragePath;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->testStoragePath = storage_path('app/test-imports');
        if (! File::exists($this->testStoragePath)) {
            File::makeDirectory($this->testStoragePath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testStoragePath)) {
            File::deleteDirectory($this->testStoragePath);
        }
        parent::tearDown();
    }

    public function test_it_fails_when_zip_file_does_not_exist(): void
    {
        $nonExistentZip = $this->testStoragePath.'/non-existent.zip';

        $this->artisan('products:import', ['zipFile' => $nonExistentZip])
            ->expectsOutput("ZIP file not found: {$nonExistentZip}")
            ->assertExitCode(1);
    }

    public function test_it_fails_when_zip_structure_is_invalid_missing_excel(): void
    {
        $zipPath = $this->createTestZip('invalid-no-excel.zip', function (ZipArchive $zip) {
            $zip->addEmptyDir('images');
            $zip->addFromString('images/sample.jpg', 'fake-image-content');
        });

        $service = new ProductImportService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required file products.xlsx is missing');

        $service->import($zipPath);
    }

    public function test_it_successfully_imports_products_when_images_folder_is_missing(): void
    {
        $zipPath = $this->createTestZip('no-images-folder.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['name', 'category', 'price', 'compare_price', 'stock_quantity', 'status', 'variants', 'multiple_images_names'],
                ['Wireless Mouse', 'Electronics', '$25.99', '$19.99', '150', 'Active', '', 'mouse1.jpg'],
            ]);
            $zip->addFromString('products.xlsx', $excelContent);
        });

        $service = new ProductImportService();
        $result = $service->import($zipPath);

        $this->assertEquals(1, $result->productsImported);
        $this->assertEquals(1, $result->missingImages);
        $this->assertDatabaseHas('products', [
            'title' => 'Wireless Mouse',
            'price' => 25.99,
        ]);
    }

    public function test_it_successfully_imports_valid_products_and_stores_images(): void
    {
        $zipPath = $this->createTestZip('valid-import.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['Name', 'SKU', 'Price', 'Description', 'Images'],
                ['iPhone 16', 'IP16', '1200', 'Apple Phone', 'iphone1.jpg, iphone2.jpg'],
                ['Samsung S25', 'SS25', '1100', 'Samsung Phone', 'samsung1.jpg'],
            ]);

            $zip->addFromString('products.xlsx', $excelContent);
            $zip->addEmptyDir('images');
            $zip->addFromString('images/iphone1.jpg', 'dummy-image-1-data');
            $zip->addFromString('images/iphone2.jpg', 'dummy-image-2-data');
            $zip->addFromString('images/samsung1.jpg', 'dummy-image-samsung-data');
        });

        $this->artisan('products:import', ['zipFile' => $zipPath])
            ->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'sku' => 'IP16',
            'title' => 'iPhone 16',
            'price' => 1200.00,
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'SS25',
            'title' => 'Samsung S25',
            'price' => 1100.00,
        ]);

        $iphone = Product::where('sku', 'IP16')->first();
        $this->assertNotNull($iphone);
        $this->assertNotNull($iphone->image);
        $this->assertCount(2, $iphone->gallery_images);
        $this->assertCount(2, $iphone->gallery_image_files);

        Storage::disk('public')->assertExists($iphone->image);
    }

    public function test_it_imports_exact_user_sheet_format_with_category_and_variants_images(): void
    {
        $zipPath = $this->createTestZip('user-sheet-import.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['name', 'category', 'price', 'compare_price', 'stock_quantity', 'status', 'variants', 'multiple_images_names'],
                [
                    'Wireless Mouse',
                    'Electronics',
                    '$25.99',
                    '$19.99',
                    '150',
                    'Active',
                    "name: Red, price: \$25.99, compare_price: \$19.99, stock_qty: 75, image_name: wireless_mouse_red.jpg\nname: Blue, price: \$25.99, compare_price: \$19.99, stock_qty: 75, image_name: wireless_mouse_blue.jpg",
                    'mouse1.jpg, mouse2.jpg',
                ],
                [
                    'Backpack',
                    'Accessories',
                    '$65.00',
                    '$55.00',
                    '0',
                    'Inactive',
                    'name: Grey, price: $65.00, compare_price: $55.00, stock_qty: 0, image_name: bp1.jpg',
                    'bp1.jpg, bp2.jpg',
                ],
            ]);

            $zip->addFromString('products.xlsx', $excelContent);
            $zip->addEmptyDir('images');
            $zip->addFromString('images/mouse1.jpg', 'mouse1-data');
            $zip->addFromString('images/mouse2.jpg', 'mouse2-data');
            $zip->addFromString('images/wireless_mouse_red.jpg', 'mouse-red-data');
            $zip->addFromString('images/wireless_mouse_blue.jpg', 'mouse-blue-data');
            $zip->addFromString('images/bp1.jpg', 'bp1-data');
            $zip->addFromString('images/bp2.jpg', 'bp2-data');
        });

        $this->artisan('products:import', ['zipFile' => $zipPath])
            ->assertExitCode(0);

        // Check Category creation
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
        $this->assertDatabaseHas('categories', ['name' => 'Accessories']);

        $category = Category::where('name', 'Electronics')->first();

        // Check Wireless Mouse Product
        $mouse = Product::where('title', 'Wireless Mouse')->first();
        $this->assertNotNull($mouse);
        $this->assertEquals($category->id, $mouse->category_id);
        $this->assertEquals(25.99, $mouse->price);
        $this->assertEquals(19.99, $mouse->compare_price);
        $this->assertEquals(150, $mouse->stock_quantity);
        $this->assertTrue($mouse->is_active);

        $this->assertCount(4, $mouse->gallery_images);

        // Check Backpack Product
        $backpack = Product::where('title', 'Backpack')->first();
        $this->assertNotNull($backpack);
        $this->assertEquals(65.00, $backpack->price);
        $this->assertEquals(55.00, $backpack->compare_price);
        $this->assertEquals(0, $backpack->stock_quantity);
        $this->assertFalse($backpack->is_active);
    }

    public function test_it_ignores_empty_rows_in_excel(): void
    {
        $zipPath = $this->createTestZip('empty-rows-import.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['Name', 'SKU', 'Price', 'Description', 'Images'],
                ['Valid Product 1', 'SKU01', '$10.00', 'Desc 1', 'pic1.jpg'],
                ['', '', '', '', ''], // Completely empty row 1
                ['Valid Product 2', 'SKU02', '$20.00', 'Desc 2', 'pic2.jpg'],
                ['   ', null, '', null, ''], // Completely empty row 2
            ]);

            $zip->addFromString('products.xlsx', $excelContent);
            $zip->addEmptyDir('images');
            $zip->addFromString('images/pic1.jpg', 'pic1');
            $zip->addFromString('images/pic2.jpg', 'pic2');
        });

        $service = new ProductImportService();
        $result = $service->import($zipPath);

        $this->assertEquals(2, $result->productsImported);
        $this->assertEquals(0, $result->rowsFailed);
        $this->assertEquals(0, $result->skippedRows);
        $this->assertEmpty($result->errors);
    }

    public function test_it_skips_invalid_rows_and_continues_import(): void
    {
        $zipPath = $this->createTestZip('validation-import.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['Name', 'SKU', 'Price', 'Description', 'Images'],
                ['Valid Product', 'VALID01', '$100.00', 'Good item', 'pic.jpg'],
                ['', 'NOSKU', '$100.00', 'Missing Name', 'pic.jpg'], // Invalid: missing name
                ['Bad Price Product', 'BADPRICE', 'not-a-number', 'Bad Price', 'pic.jpg'], // Invalid: non-numeric price
            ]);

            $zip->addFromString('products.xlsx', $excelContent);
            $zip->addEmptyDir('images');
            $zip->addFromString('images/pic.jpg', 'dummy-pic');
        });

        $service = new ProductImportService();
        $result = $service->import($zipPath);

        $this->assertEquals(1, $result->productsImported);
        $this->assertEquals(2, $result->rowsFailed);
        $this->assertEquals(2, $result->skippedRows);
        $this->assertCount(2, $result->errors);

        $this->assertDatabaseHas('products', ['sku' => 'VALID01']);
        $this->assertDatabaseMissing('products', ['sku' => 'NOSKU']);
        $this->assertDatabaseMissing('products', ['sku' => 'BADPRICE']);
    }

    public function test_it_reports_missing_images_and_continues_importing_product(): void
    {
        $zipPath = $this->createTestZip('missing-images.zip', function (ZipArchive $zip) {
            $excelContent = $this->createExcelCsvContent([
                ['Name', 'SKU', 'Price', 'Description', 'Images'],
                ['Laptop Pro', 'LAP01', '$2000.00', 'High end laptop', 'laptop.jpg, non_existent.jpg'],
            ]);

            $zip->addFromString('products.xlsx', $excelContent);
            $zip->addEmptyDir('images');
            $zip->addFromString('images/laptop.jpg', 'laptop-image-content');
        });

        $service = new ProductImportService();
        $result = $service->import($zipPath);

        $this->assertEquals(1, $result->productsImported);
        $this->assertEquals(1, $result->missingImages);
        $this->assertContains('Row 2: Image non_existent.jpg not found.', $result->errors);

        $product = Product::where('sku', 'LAP01')->first();
        $this->assertNotNull($product);
        $this->assertCount(1, $product->gallery_images);
    }

    /**
     * Helper method to create a test ZIP file.
     */
    protected function createTestZip(string $filename, callable $buildZip): string
    {
        $zipPath = $this->testStoragePath.'/'.$filename;
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $buildZip($zip);

        $zip->close();

        return $zipPath;
    }

    /**
     * Helper method to generate valid XLSX file binary content.
     */
    protected function createExcelCsvContent(array $rows): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempPath);
        $content = (string) file_get_contents($tempPath);
        @unlink($tempPath);

        return $content;
    }
}
