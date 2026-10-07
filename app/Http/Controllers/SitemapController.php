<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\SettingStore;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $siteName = SettingStore::get('public_site.name', config('app.name', 'RH Commerce'));
        $appUrl = config('app.url');

        $staticPages = [
            ['loc' => $appUrl . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $appUrl . '/shop', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $appUrl . '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/reviews', 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => $appUrl . '/custom-order', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/faq', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/terms', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/privacy-policy', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/shipping-policy', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $appUrl . '/returns', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $productUrls = [];
        $categoryUrls = [];

        if (Schema::hasTable('products') && Schema::hasTable('categories')) {
            $products = Product::query()
                ->select('slug', 'updated_at', 'image')
                ->active()
                ->latest('updated_at')
                ->get();

            foreach ($products as $product) {
                $productUrls[] = [
                    'loc' => $appUrl . '/product/' . $product->slug,
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at?->toW3cString(),
                    'image' => $product->imageUrl(),
                ];
            }

            $categories = Category::query()
                ->select('slug', 'updated_at')
                ->active()
                ->get();

            foreach ($categories as $category) {
                $categoryUrls[] = [
                    'loc' => $appUrl . '/shop?category=' . $category->slug,
                    'priority' => '0.5',
                    'changefreq' => 'weekly',
                    'lastmod' => $category->updated_at?->toW3cString(),
                ];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$page['loc']}</loc>\n";
            $xml .= "    <priority>{$page['priority']}</priority>\n";
            $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
            $xml .= "  </url>\n";
        }

        foreach ($categoryUrls as $cat) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$cat['loc']}</loc>\n";
            $xml .= "    <priority>{$cat['priority']}</priority>\n";
            $xml .= "    <changefreq>{$cat['changefreq']}</changefreq>\n";
            if ($cat['lastmod']) {
                $xml .= "    <lastmod>{$cat['lastmod']}</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        foreach ($productUrls as $prod) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$prod['loc']}</loc>\n";
            $xml .= "    <priority>{$prod['priority']}</priority>\n";
            $xml .= "    <changefreq>{$prod['changefreq']}</changefreq>\n";
            if ($prod['lastmod']) {
                $xml .= "    <lastmod>{$prod['lastmod']}</lastmod>\n";
            }
            if ($prod['image']) {
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>{$prod['image']}</image:loc>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
