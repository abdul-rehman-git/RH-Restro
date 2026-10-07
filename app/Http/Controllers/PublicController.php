<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPayloads;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Support\SeoService;
use App\Support\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class PublicController extends Controller
{
    use BuildsPublicPayloads;

    public function home()
    {
        $home = SettingStore::homePage();

        $categories = $this->hasCatalogTables()
            ? Category::query()
                ->active()
                ->featured()
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(6)
                ->get()
            : collect();

        $products = $this->hasCatalogTables()
            ? Product::query()
                ->with('category:id,name,slug')
                ->withApprovedReviewStats()
                ->active()
                ->orderBy('sort_order')
                ->latest()
                ->limit(16)
                ->get()
            : collect();

        $testimonials = $this->hasReviewsTable()
            ? Review::query()
                ->approved()
                ->where('is_featured', true)
                ->latest('reviewed_at')
                ->limit(6)
                ->get()
            : collect();

        $sitePayload = $this->sitePayload();

        return Inertia::render('Public/Home', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('home', [
                'image' => $this->assetUrl($home['hero_image']['url'] ?? $home['hero_image_path'] ?? null),
                'jsonLd' => [
                    SeoService::organizationJsonLd($sitePayload),
                    SeoService::websiteJsonLd($sitePayload),
                ],
            ]),
            'home' => [
                ...$this->homeContentPayload($home),
                'categories' => $categories->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $this->assetUrl($category->image),
                    'item_count' => $category->products_count,
                    'link' => '/shop?category='.$category->slug,
                ])->all(),
                'products' => $products->map(fn (Product $product): array => $this->productCardPayload($product))->all(),
                'testimonials' => $testimonials->map(fn (Review $review): array => [
                    'id' => $review->id,
                    'name' => $review->reviewer_name,
                    'content' => $review->comment,
                    'rating' => $review->rating,
                ])->all(),
            ],
        ]);
    }

    public function shop(Request $request)
    {
        $sitePayload = $this->sitePayload();
        $crumbs = [
            ['label' => 'Home', 'url' => config('app.url') . '/'],
            ['label' => 'Shop', 'url' => config('app.url') . '/shop'],
        ];

        if (! $this->hasCatalogTables()) {
            return Inertia::render('Public/Shop', [
                'site' => $sitePayload,
                'seo' => $this->seoPayload('shop', [
                    'jsonLd' => [SeoService::breadcrumbJsonLd($crumbs)],
                ]),
                'products' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 9,
                    'total' => 0,
                    'data' => [],
                ],
                'categories' => [],
                'filters' => [
                    'category' => $request->string('category')->toString(),
                    'max_price' => $request->input('max_price', 1000),
                    'sort' => $request->string('sort')->toString() ?: 'featured',
                ],
            ]);
        }

        $query = Product::query()
            ->with('category:id,name,slug')
            ->withApprovedReviewStats()
            ->active()
            ->categorySlug($request->string('category')->toString())
            ->priceBetween($request->input('min_price'), $request->input('max_price'))
            ->search($request->string('search')->toString());

        match ($request->string('sort')->toString()) {
            'price-low' => $query->orderBy('price'),
            'price-high' => $query->orderByDesc('price'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->latest(),
        };

        $products = $query->paginate(9);
        $categories = Category::query()->active()->orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Public/Shop', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('shop', [
                'jsonLd' => [SeoService::breadcrumbJsonLd($crumbs)],
            ]),
            'products' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'data' => $products->getCollection()->map(fn (Product $product): array => $this->productCardPayload($product))->values()->all(),
            ],
            'categories' => $categories->map(fn (Category $category): array => [
                'id' => $category->id,
                'slug' => $category->slug,
                'label' => $category->name,
            ])->all(),
            'filters' => [
                'category' => $request->string('category')->toString(),
                'max_price' => $request->input('max_price', 1000),
                'sort' => $request->string('sort')->toString() ?: 'featured',
            ],
        ]);
    }

    public function product(string $slug)
    {
        abort_unless($this->hasCatalogTables(), 404);

        $product = Product::query()
            ->with(['category:id,name,slug', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $reviewQuery = Review::query()
            ->approved()
            ->where('product_id', $product->id)
            ->latest('reviewed_at');

        $reviews = (clone $reviewQuery)->limit(3)->get();
        $reviewsCount = (clone $reviewQuery)->count();
        $averageRating = $reviewsCount > 0 ? round((float) (clone $reviewQuery)->avg('rating'), 1) : 0;

        $relatedProducts = Product::query()
            ->with('category:id,name,slug')
            ->withApprovedReviewStats()
            ->active()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $productPayload = [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'price' => (float) $product->price,
            'original_price' => $product->compare_price ? (float) $product->compare_price : null,
            'category' => $product->category?->name,
            'category_slug' => $product->category?->slug,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'badge_label' => $product->badge_label,
            'rating' => $averageRating,
            'reviews_count' => $reviewsCount,
            'in_stock' => $product->stock_quantity > 0,
            'stock_quantity' => $product->stock_quantity,
            'images' => array_map(fn (string $path): ?string => $this->assetUrl($path), $product->imagePaths()),
            'features' => $product->feature_points ?? [],
            'care_instructions' => $product->care_instructions,
            'shipping_note' => $product->shipping_note,
            'variants' => $product->variants->map(fn ($variant): array => [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'original_price' => $variant->compare_price ? (float) $variant->compare_price : null,
                'stock_quantity' => $variant->stock_quantity,
                'in_stock' => $variant->stock_quantity > 0,
                'image' => $this->assetUrl($variant->image),
            ])->values()->all(),
        ];

        $crumbs = [
            ['label' => 'Home', 'url' => config('app.url') . '/'],
            ['label' => 'Shop', 'url' => config('app.url') . '/shop'],
            ['label' => $product->title, 'url' => config('app.url') . '/product/' . $product->slug],
        ];

        return Inertia::render('Public/ProductDetail', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('product', [
                'product' => $productPayload,
                'image' => $productPayload['images'][0] ?? null,
                'description' => $product->short_description ?: $product->description,
                'jsonLd' => [
                    SeoService::productJsonLd($productPayload),
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
            'product' => $productPayload,
            'reviews' => $reviews->map(fn (Review $review): array => [
                'id' => $review->id,
                'name' => $review->reviewer_name,
                'rating' => $review->rating,
                'date' => $review->reviewed_at?->format('F d, Y'),
                'comment' => $review->comment,
                'images' => $this->reviewImageUrls($review),
                'verified' => $review->is_verified_purchase,
                'helpful' => $review->helpful_count,
            ])->all(),
            'relatedProducts' => $relatedProducts->map(
                fn (Product $relatedProduct): array => $this->productCardPayload($relatedProduct),
            )->all(),
        ]);
    }

    public function about()
    {
        $about = SettingStore::aboutPage();
        $sitePayload = $this->sitePayload();
        $aboutPayload = $this->aboutContentPayload($about);

        return Inertia::render('Public/About', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('about', [
                'description' => $about['hero_subtitle'] ?? '',
                'image' => $aboutPayload['artist_image_url'] ?? null,
                'jsonLd' => [
                    SeoService::organizationJsonLd($sitePayload),
                    SeoService::articleJsonLd($aboutPayload),
                ],
            ]),
            'about' => $aboutPayload,
        ]);
    }

    public function contact()
    {
        $sitePayload = $this->sitePayload();

        return Inertia::render('Public/Contact', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('contact', [
                'jsonLd' => [
                    SeoService::localBusinessJsonLd($sitePayload),
                ],
            ]),
        ]);
    }

    public function orderTracking()
    {
        return Inertia::render('Public/OrderTracking', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('order-tracking'),
        ]);
    }

    public function reviews(Request $request)
    {
        $productSlug = $request->string('product')->toString();
        $selectedProduct = filled($productSlug)
            ? Product::query()
                ->select('id', 'title', 'slug')
                ->where('slug', $productSlug)
                ->first()
            : null;

        if (! $this->hasReviewsTable()) {
            return Inertia::render('Public/Reviews', [
                'site' => $this->sitePayload(),
                'seo' => $this->seoPayload('reviews', [
                    'product' => $selectedProduct ? ['title' => $selectedProduct->title] : [],
                ]),
                'productFilter' => $selectedProduct
                    ? [
                        'title' => $selectedProduct->title,
                        'slug' => $selectedProduct->slug,
                    ]
                    : null,
                'summary' => [
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'breakdown' => collect(range(5, 1))
                        ->map(fn (int $stars): array => ['stars' => $stars, 'count' => 0])
                        ->values()
                        ->all(),
                ],
                'reviews' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 8,
                    'total' => 0,
                ],
            ]);
        }

        $reviewQuery = Review::query()
            ->with('product:id,title,slug')
            ->approved()
            ->when(
                $selectedProduct,
                fn ($query) => $query->where('product_id', $selectedProduct->id),
            )
            ->latest('reviewed_at')
            ->latest();

        $reviews = (clone $reviewQuery)
            ->paginate(8)
            ->withQueryString();

        $allApproved = (clone $reviewQuery)->get();

        return Inertia::render('Public/Reviews', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('reviews', [
                'product' => $selectedProduct ? ['title' => $selectedProduct->title] : [],
            ]),
            'productFilter' => $selectedProduct
                ? [
                    'title' => $selectedProduct->title,
                    'slug' => $selectedProduct->slug,
                ]
                : null,
            'summary' => [
                'average_rating' => $allApproved->isNotEmpty() ? round((float) $allApproved->avg('rating'), 1) : 0,
                'total_reviews' => $allApproved->count(),
                'breakdown' => collect(range(5, 1))
                    ->map(fn (int $stars): array => [
                        'stars' => $stars,
                        'count' => $allApproved->where('rating', $stars)->count(),
                    ])
                    ->values()
                    ->all(),
            ],
            'reviews' => [
                'data' => $reviews->getCollection()->map(fn (Review $review): array => [
                    'id' => $review->id,
                    'name' => $review->reviewer_name,
                    'rating' => $review->rating,
                    'date' => $review->reviewed_at?->format('F d, Y'),
                    'comment' => $review->comment,
                    'images' => $this->reviewImageUrls($review),
                    'verified' => $review->is_verified_purchase,
                    'helpful' => $review->helpful_count,
                    'product' => $review->product
                        ? [
                            'title' => $review->product->title,
                            'slug' => $review->product->slug,
                        ]
                        : null,
                ])->values()->all(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function cart()
    {
        return Inertia::render('Public/Cart', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('cart'),
        ]);
    }

    public function checkout()
    {
        return Inertia::render('Public/Checkout', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('checkout'),
        ]);
    }

    public function customOrder()
    {
        $sitePayload = $this->sitePayload();
        $processSteps = SettingStore::get('public_pages.about.process_steps', []);

        return Inertia::render('Public/CustomOrder', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('custom-order', [
                'jsonLd' => $processSteps ? [SeoService::faqJsonLd($processSteps)] : [],
            ]),
        ]);
    }

    public function login()
    {
        if (Auth::guard('customer')->check()) {
            return redirect('/');
        }

        return Inertia::render('Public/Login', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('sign-in'),
        ]);
    }

    public function faq()
    {
        return Inertia::render('Public/Faq', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('faq'),
        ]);
    }

    public function terms()
    {
        return Inertia::render('Public/Terms', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('terms'),
        ]);
    }

    public function privacyPolicy()
    {
        return Inertia::render('Public/PrivacyPolicy', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('privacy-policy'),
        ]);
    }

    public function shippingPolicy()
    {
        return Inertia::render('Public/ShippingPolicy', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('shipping-policy'),
        ]);
    }

    public function returns()
    {
        return Inertia::render('Public/Returns', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('returns'),
        ]);
    }

    protected function hasCatalogTables(): bool
    {
        return Schema::hasTable('categories') && Schema::hasTable('products');
    }

    protected function hasReviewsTable(): bool
    {
        return Schema::hasTable('reviews');
    }
}
