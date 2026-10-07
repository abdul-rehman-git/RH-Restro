<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_rich_seo_tags_and_json_ld(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->has('seo')
            ->where('seo.robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')
            ->has('seo.title')
            ->has('seo.description')
            ->has('seo.canonical')
            ->has('seo.og')
            ->has('seo.twitter')
            ->has('seo.jsonLd', 3) // Organization, WebSite, Restaurant
            ->where('seo.jsonLd.0.@type', 'Organization')
            ->where('seo.jsonLd.1.@type', 'WebSite')
            ->where('seo.jsonLd.2.@type', 'Restaurant')
        );

        // Verify server-side rendered HTML includes meta tags and JSON-LD
        $html = $response->getContent();
        $this->assertStringContainsString('<meta name="description"', $html);
        $this->assertStringContainsString('<meta name="robots"', $html);
        $this->assertStringContainsString('<meta property="og:title"', $html);
        $this->assertStringContainsString('<meta property="og:description"', $html);
        $this->assertStringContainsString('<meta property="og:image"', $html);
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('sitemap.xml', $html);
    }

    public function test_shop_page_returns_menu_breadcrumbs_and_item_list_schema(): void
    {
        $category = Category::create([
            'name' => 'Artisan Pizzas',
            'slug' => 'artisan-pizzas',
            'description' => 'Stone-baked artisan pizzas',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Margherita Pizza',
            'slug' => 'margherita-pizza',
            'price' => 18.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $response = $this->get('/shop?category=artisan-pizzas');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Shop')
            ->has('seo')
            ->where('seo.title', fn ($title) => str_contains((string) $title, 'Artisan Pizzas'))
            ->has('seo.jsonLd', 2) // BreadcrumbList and ItemList
            ->where('seo.jsonLd.0.@type', 'BreadcrumbList')
            ->where('seo.jsonLd.1.@type', 'ItemList')
        );
    }

    public function test_product_page_returns_product_and_menu_item_schema(): void
    {
        $category = Category::create([
            'name' => 'Steaks & Grills',
            'slug' => 'steaks-grills',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Prime Angus Ribeye Steak',
            'slug' => 'prime-angus-ribeye-steak',
            'price' => 45.00,
            'short_description' => '28-day aged ribeye steak cooked to perfection',
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        $response = $this->get('/product/' . $product->slug);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/ProductDetail')
            ->has('seo')
            ->where('seo.title', fn ($title) => str_contains((string) $title, 'Prime Angus Ribeye Steak'))
            ->has('seo.canonical')
            ->has('seo.jsonLd', 2) // Product and BreadcrumbList
            ->where('seo.jsonLd.0.@type', ['Product', 'MenuItem'])
            ->where('seo.jsonLd.0.name', 'Prime Angus Ribeye Steak')
            ->where('seo.jsonLd.0.offers.@type', 'Offer')
            ->where('seo.jsonLd.0.offers.price', '45.00')
            ->where('seo.jsonLd.0.offers.availability', 'https://schema.org/InStock')
            ->where('seo.jsonLd.1.@type', 'BreadcrumbList')
        );
    }

    public function test_faq_page_returns_faq_page_schema(): void
    {
        $response = $this->get('/faq');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Faq')
            ->has('seo')
            ->has('seo.jsonLd', 2) // FAQPage and BreadcrumbList
            ->where('seo.jsonLd.0.@type', 'FAQPage')
            ->has('seo.jsonLd.0.mainEntity')
            ->where('seo.jsonLd.1.@type', 'BreadcrumbList')
        );
    }

    public function test_about_page_returns_organization_and_article_schema(): void
    {
        $response = $this->get('/about');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/About')
            ->has('seo')
            ->has('seo.jsonLd', 3) // Organization, AboutPage, BreadcrumbList
            ->where('seo.jsonLd.0.@type', 'Organization')
            ->where('seo.jsonLd.1.@type', 'AboutPage')
            ->where('seo.jsonLd.2.@type', 'BreadcrumbList')
        );
    }

    public function test_contact_page_returns_restaurant_and_breadcrumb_schema(): void
    {
        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Contact')
            ->has('seo')
            ->has('seo.jsonLd', 2) // Restaurant, BreadcrumbList
            ->where('seo.jsonLd.0.@type', 'Restaurant')
            ->where('seo.jsonLd.1.@type', 'BreadcrumbList')
        );
    }

    public function test_sitemap_xml_generates_valid_xml_with_pages_and_images(): void
    {
        $category = Category::create([
            'name' => 'Gourmet Burgers',
            'slug' => 'gourmet-burgers',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Truffle Smash Burger',
            'slug' => 'truffle-smash-burger',
            'price' => 16.50,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $xml = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $xml);
        $this->assertStringContainsString('/shop', $xml);
        $this->assertStringContainsString('/about', $xml);
        $this->assertStringContainsString('/contact', $xml);
        $this->assertStringContainsString('/reviews', $xml);
        $this->assertStringContainsString('/faq', $xml);
        $this->assertStringContainsString('/product/truffle-smash-burger', $xml);
        $this->assertStringContainsString('/shop?category=gourmet-burgers', $xml);

        // Verify it is syntactically valid XML
        $dom = new \DOMDocument();
        $isValid = $dom->loadXML($xml);
        $this->assertTrue($isValid, 'Sitemap is not valid XML');
    }

    public function test_robots_txt_allows_public_pages_and_disallows_private_routes(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Allow: /shop', $content);
        $this->assertStringContainsString('Allow: /product/', $content);
        $this->assertStringContainsString('Disallow: /cart', $content);
        $this->assertStringContainsString('Disallow: /checkout', $content);
        $this->assertStringContainsString('Disallow: /admin-reviews/', $content);
        $this->assertStringContainsString('Sitemap:', $content);
        $this->assertStringContainsString('/sitemap.xml', $content);
    }

    public function test_manifest_json_is_valid_and_accessible(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $json = json_decode((string) file_get_contents($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertSame('RH Restro - Fine Dining & Gourmet Restaurant', $json['name']);
        $this->assertSame('RH Restro', $json['short_name']);
        $this->assertNotEmpty($json['icons']);
    }

    public function test_html_sitemap_page_renders_with_seo_and_categories(): void
    {
        $category = Category::create([
            'name' => 'Signature Pasta',
            'slug' => 'signature-pasta',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Truffle Fettuccine',
            'slug' => 'truffle-fettuccine',
            'price' => 24.00,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->get('/sitemap');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Sitemap')
            ->has('seo')
            ->where('seo.title', fn ($t) => str_contains((string) $t, 'Sitemap'))
            ->has('seo.jsonLd', 1)
            ->where('seo.jsonLd.0.@type', 'BreadcrumbList')
            ->has('categories')
            ->has('featuredDishes')
            ->where('xmlSitemapUrl', url('/sitemap.xml'))
        );
    }

    public function test_artisan_sitemap_generate_command_creates_physical_files(): void
    {
        $sitemapPath = public_path('sitemap.xml');
        $robotsPath = public_path('robots.txt');

        $this->artisan('sitemap:generate')
            ->assertSuccessful()
            ->expectsOutputToContain('Sitemap generated successfully!');

        $this->assertFileExists($sitemapPath);
        $this->assertFileExists($robotsPath);

        $sitemapXml = (string) file_get_contents($sitemapPath);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $sitemapXml);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $sitemapXml);
        $this->assertStringContainsString('/sitemap', $sitemapXml);

        $robotsTxt = (string) file_get_contents($robotsPath);
        $this->assertStringContainsString('Allow: /sitemap', $robotsTxt);
        $this->assertStringContainsString('Sitemap:', $robotsTxt);
    }
}

