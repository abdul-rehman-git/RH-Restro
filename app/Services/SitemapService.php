<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Support\ImageUrl;
use App\Support\SettingStore;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SitemapService
{
    /**
     * Generate the XML sitemap string.
     */
    public function generateXml(): string
    {
        $siteName = SettingStore::get('public_site.name', config('app.name', 'RH Restro'));
        $appUrl = rtrim(config('app.url'), '/');
        $now = now()->toW3cString();

        $staticPages = [
            ['loc' => $appUrl . '/', 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => $appUrl . '/shop', 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
            ['loc' => $appUrl . '/sitemap', 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => $appUrl . '/about', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => $appUrl . '/contact', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => $appUrl . '/reviews', 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => $appUrl . '/custom-order', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => $appUrl . '/faq', 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => $appUrl . '/terms', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $now],
            ['loc' => $appUrl . '/privacy-policy', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $now],
            ['loc' => $appUrl . '/shipping-policy', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $now],
            ['loc' => $appUrl . '/returns', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $now],
        ];

        $productUrls = [];
        $categoryUrls = [];

        if (Schema::hasTable('products') && Schema::hasTable('categories')) {
            $products = Product::query()
                ->select('slug', 'title', 'short_description', 'updated_at', 'image', 'gallery_images')
                ->active()
                ->latest('updated_at')
                ->get();

            foreach ($products as $product) {
                $image = $product->imageUrl();
                $productUrls[] = [
                    'loc' => $appUrl . '/product/' . $product->slug,
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at?->toW3cString() ?: $now,
                    'image' => $image,
                    'title' => $product->title,
                    'caption' => $product->short_description ?: "{$product->title} at {$siteName}",
                ];
            }

            $categories = Category::query()
                ->select('slug', 'name', 'description', 'updated_at', 'image')
                ->active()
                ->get();

            foreach ($categories as $category) {
                $catImage = ImageUrl::resolve($category->image);
                $categoryUrls[] = [
                    'loc' => $appUrl . '/shop?category=' . $category->slug,
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $category->updated_at?->toW3cString() ?: $now,
                    'image' => $catImage,
                    'title' => $category->name,
                ];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->escapeXml($page['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $this->escapeXml($page['lastmod']) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $this->escapeXml($page['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . $this->escapeXml($page['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }

        foreach ($categoryUrls as $cat) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->escapeXml($cat['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $this->escapeXml($cat['lastmod']) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $this->escapeXml($cat['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . $this->escapeXml($cat['priority']) . "</priority>\n";
            if (!empty($cat['image'])) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>' . $this->escapeXml($cat['image']) . "</image:loc>\n";
                $xml .= '      <image:title>' . $this->escapeXml($cat['title']) . "</image:title>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        foreach ($productUrls as $prod) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->escapeXml($prod['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $this->escapeXml($prod['lastmod']) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $this->escapeXml($prod['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . $this->escapeXml($prod['priority']) . "</priority>\n";
            if (!empty($prod['image'])) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>' . $this->escapeXml($prod['image']) . "</image:loc>\n";
                $xml .= '      <image:title>' . $this->escapeXml($prod['title']) . "</image:title>\n";
                $xml .= '      <image:caption>' . $this->escapeXml($prod['caption']) . "</image:caption>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate and save public/sitemap.xml to disk.
     *
     * @return array{path: string, urls_count: int, images_count: int, size_bytes: int}
     */
    public function generateAndSave(?string $path = null): array
    {
        $targetPath = $path ?: public_path('sitemap.xml');
        $xml = $this->generateXml();

        File::put($targetPath, $xml);

        $urlsCount = substr_count($xml, '<loc>');
        $imagesCount = substr_count($xml, '<image:loc>');

        return [
            'path' => $targetPath,
            'urls_count' => $urlsCount,
            'images_count' => $imagesCount,
            'size_bytes' => strlen($xml),
        ];
    }

    /**
     * Generate and save public/robots.txt to disk.
     */
    public function generateRobotsTxt(?string $path = null): string
    {
        $targetPath = $path ?: public_path('robots.txt');
        $appUrl = rtrim(config('app.url'), '/');

        $content = "User-agent: *\n"
            . "Allow: /\n"
            . "Allow: /shop\n"
            . "Allow: /product/\n"
            . "Allow: /about\n"
            . "Allow: /contact\n"
            . "Allow: /reviews\n"
            . "Allow: /custom-order\n"
            . "Allow: /faq\n"
            . "Allow: /sitemap\n"
            . "Allow: /terms\n"
            . "Allow: /privacy-policy\n"
            . "Allow: /shipping-policy\n"
            . "Allow: /returns\n"
            . "Allow: /build/\n"
            . "Allow: /storage/\n\n"
            . "Disallow: /cart\n"
            . "Disallow: /checkout\n"
            . "Disallow: /sign-in\n"
            . "Disallow: /order-tracking\n"
            . "Disallow: /my-account\n"
            . "Disallow: /dashboard\n"
            . "Disallow: /profile\n"
            . "Disallow: /settings\n"
            . "Disallow: /public-content\n"
            . "Disallow: /categories/\n"
            . "Disallow: /products/\n"
            . "Disallow: /customers/\n"
            . "Disallow: /orders/\n"
            . "Disallow: /payments/\n"
            . "Disallow: /contact-inquiries/\n"
            . "Disallow: /admin-reviews/\n"
            . "Disallow: /api/\n"
            . "Disallow: /auth/\n"
            . "Disallow: /login\n"
            . "Disallow: /register\n"
            . "Disallow: /forgot-password\n"
            . "Disallow: /reset-password\n\n"
            . "Sitemap: {$appUrl}/sitemap.xml\n";

        File::put($targetPath, $content);

        return $content;
    }

    private function escapeXml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}

