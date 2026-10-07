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
                    SeoService::localBusinessJsonLd($sitePayload),
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
        $categorySlug = $request->string('category')->toString();
        $selectedCategory = null;

        if ($this->hasCatalogTables() && filled($categorySlug)) {
            $selectedCategory = Category::query()->where('slug', $categorySlug)->first();
        }

        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Our Menu', 'url' => url('/shop')],
        ];

        $pageTitle = null;
        $pageDescription = null;

        if ($selectedCategory) {
            $crumbs[] = [
                'label' => $selectedCategory->name,
                'url' => url('/shop?category=' . $selectedCategory->slug),
            ];
            $pageTitle = "{$selectedCategory->name} Menu — {$sitePayload['name']}";
            $pageDescription = filled($selectedCategory->description)
                ? $selectedCategory->description
                : "Explore our delicious {$selectedCategory->name} selection at {$sitePayload['name']}. Savor artisan culinary creations prepared fresh to order.";
        }

        if (! $this->hasCatalogTables()) {
            return Inertia::render('Public/Shop', [
                'site' => $sitePayload,
                'seo' => $this->seoPayload('shop', [
                    'title' => $pageTitle,
                    'description' => $pageDescription,
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
                    'category' => $categorySlug,
                    'max_price' => $request->input('max_price', 1000),
                    'sort' => $request->string('sort')->toString() ?: 'featured',
                ],
            ]);
        }

        $query = Product::query()
            ->with('category:id,name,slug')
            ->withApprovedReviewStats()
            ->active()
            ->categorySlug($categorySlug)
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

        $dishListItems = $products->getCollection()->map(fn (Product $p): array => [
            'slug' => $p->slug,
            'title' => $p->title,
        ])->all();

        return Inertia::render('Public/Shop', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('shop', [
                'title' => $pageTitle,
                'description' => $pageDescription,
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                    SeoService::itemListJsonLd(
                        $dishListItems,
                        $selectedCategory ? "{$selectedCategory->name} Menu" : "Our Gourmet Menu",
                        $request->fullUrl(),
                    ),
                ],
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
                'category' => $categorySlug,
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
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Our Menu', 'url' => url('/shop')],
        ];
        if ($product->category) {
            $crumbs[] = [
                'label' => $product->category->name,
                'url' => url('/shop?category=' . $product->category->slug),
            ];
        }
        $crumbs[] = [
            'label' => $product->title,
            'url' => url('/product/' . $product->slug),
        ];

        $sitePayload = $this->sitePayload();

        return Inertia::render('Public/ProductDetail', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('product', [
                'product' => $productPayload,
                'image' => $productPayload['images'][0] ?? null,
                'description' => $product->short_description ?: $product->description,
                'jsonLd' => [
                    SeoService::productJsonLd($productPayload, $sitePayload['name']),
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
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Our Story', 'url' => url('/about')],
        ];

        return Inertia::render('Public/About', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('about', [
                'description' => $about['hero_subtitle'] ?? '',
                'image' => $aboutPayload['artist_image_url'] ?? null,
                'jsonLd' => [
                    SeoService::organizationJsonLd($sitePayload),
                    SeoService::articleJsonLd($aboutPayload),
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
            'about' => $aboutPayload,
        ]);
    }

    public function contact()
    {
        $sitePayload = $this->sitePayload();
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Contact Us', 'url' => url('/contact')],
        ];

        return Inertia::render('Public/Contact', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('contact', [
                'description' => "Contact {$sitePayload['name']} for table reservations, private event catering, chef specials, and dining support. Call, WhatsApp, or visit us today.",
                'jsonLd' => [
                    SeoService::localBusinessJsonLd($sitePayload),
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function orderTracking()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Order Tracking', 'url' => url('/order-tracking')],
        ];

        return Inertia::render('Public/OrderTracking', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('order-tracking', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
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

        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Guest Reviews', 'url' => url('/reviews')],
        ];

        if ($selectedProduct) {
            $crumbs[] = [
                'label' => $selectedProduct->title,
                'url' => url('/reviews?product=' . $selectedProduct->slug),
            ];
        }

        if (! $this->hasReviewsTable()) {
            return Inertia::render('Public/Reviews', [
                'site' => $this->sitePayload(),
                'seo' => $this->seoPayload('reviews', [
                    'product' => $selectedProduct ? ['title' => $selectedProduct->title] : [],
                    'jsonLd' => [
                        SeoService::breadcrumbJsonLd($crumbs),
                    ],
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
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
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
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Cart', 'url' => url('/cart')],
        ];

        return Inertia::render('Public/Cart', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('cart', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function checkout()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Checkout', 'url' => url('/checkout')],
        ];

        return Inertia::render('Public/Checkout', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('checkout', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function customOrder()
    {
        $sitePayload = $this->sitePayload();
        $processSteps = SettingStore::get('public_pages.about.process_steps', []);
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Table Reservations & Catering', 'url' => url('/custom-order')],
        ];

        $jsonLd = [
            SeoService::localBusinessJsonLd($sitePayload),
            SeoService::breadcrumbJsonLd($crumbs),
        ];

        if (!empty($processSteps)) {
            $jsonLd[] = SeoService::faqJsonLd($processSteps);
        }

        return Inertia::render('Public/CustomOrder', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('custom-order', [
                'description' => "Reserve your table or plan private dining catering with {$sitePayload['name']}. Personalized chef menus, private event rooms, and warm hospitality.",
                'jsonLd' => $jsonLd,
            ]),
        ]);
    }

    public function login()
    {
        if (Auth::guard('customer')->check()) {
            return redirect('/');
        }

        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Sign In', 'url' => url('/sign-in')],
        ];

        return Inertia::render('Public/Login', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('sign-in', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function faq()
    {
        $sitePayload = $this->sitePayload();
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Frequently Asked Questions', 'url' => url('/faq')],
        ];

        $faqs = [
            [
                'q' => 'What dining and payment options do you accept?',
                'a' => 'We welcome dine-in, takeaway, and online delivery orders. We accept all major credit/debit cards (Visa, MasterCard, Amex), PayPal, cash, and secure online payment gateways.',
                'title' => 'What dining and payment options do you accept?',
                'description' => 'We welcome dine-in, takeaway, and online delivery orders. We accept all major credit/debit cards (Visa, MasterCard, Amex), PayPal, cash, and secure online payment gateways.',
            ],
            [
                'q' => 'How does hot express food delivery work?',
                'a' => 'All delivery orders are prepared hot and packed in temperature-sealed, heat-locking containers. Delivery typically arrives within 30-45 minutes depending on destination.',
                'title' => 'How does hot express food delivery work?',
                'description' => 'All delivery orders are prepared hot and packed in temperature-sealed, heat-locking containers. Delivery typically arrives within 30-45 minutes depending on destination.',
            ],
            [
                'q' => 'Are all meats and ingredients halal certified?',
                'a' => 'Yes, 100% of our meat cuts, chicken, and ingredients are certified Halal and sourced fresh daily from verified organic suppliers.',
                'title' => 'Are all meats and ingredients halal certified?',
                'description' => 'Yes, 100% of our meat cuts, chicken, and ingredients are certified Halal and sourced fresh daily from verified organic suppliers.',
            ],
            [
                'q' => 'How can I reserve a table for lunch or dinner?',
                'a' => 'You can easily reserve a table online via our Reservations page, call our host desk directly, or message us on WhatsApp for instant confirmation.',
                'title' => 'How can I reserve a table for lunch or dinner?',
                'description' => 'You can easily reserve a table online via our Reservations page, call our host desk directly, or message us on WhatsApp for instant confirmation.',
            ],
            [
                'q' => 'Do you provide private catering and event hosting?',
                'a' => 'Yes! We cater corporate lunches, birthday parties, weddings, and private dinners with customized chef menus, live stations, and full dining setup.',
                'title' => 'Do you provide private catering and event hosting?',
                'description' => 'Yes! We cater corporate lunches, birthday parties, weddings, and private dinners with customized chef menus, live stations, and full dining setup.',
            ],
            [
                'q' => 'Can I track my online order in real time?',
                'a' => 'Yes, once you place an order, you will receive an order number to track kitchen preparation, dispatch, and delivery status on our Order Tracking page.',
                'title' => 'Can I track my online order in real time?',
                'description' => 'Yes, once you place an order, you will receive an order number to track kitchen preparation, dispatch, and delivery status on our Order Tracking page.',
            ],
            [
                'q' => 'What is your cancellation and refund policy?',
                'a' => 'Orders can be modified or cancelled before kitchen preparation begins. If you experience any quality issue, our manager will issue a prompt replacement or full refund.',
                'title' => 'What is your cancellation and refund policy?',
                'description' => 'Orders can be modified or cancelled before kitchen preparation begins. If you experience any quality issue, our manager will issue a prompt replacement or full refund.',
            ],
            [
                'q' => 'Do you accommodate allergies and dietary restrictions?',
                'a' => 'Our master chefs gladly customize dishes for gluten-free, vegetarian, nut-free, or dairy-free preferences. Please add a note to your order or inform your server.',
                'title' => 'Do you accommodate allergies and dietary restrictions?',
                'description' => 'Our master chefs gladly customize dishes for gluten-free, vegetarian, nut-free, or dairy-free preferences. Please add a note to your order or inform your server.',
            ],
        ];

        return Inertia::render('Public/Faq', [
            'site' => $sitePayload,
            'faqs' => $faqs,
            'seo' => $this->seoPayload('faq', [
                'jsonLd' => [
                    SeoService::faqJsonLd($faqs),
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function terms()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Terms of Service', 'url' => url('/terms')],
        ];

        return Inertia::render('Public/Terms', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('terms', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function privacyPolicy()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Privacy Policy', 'url' => url('/privacy-policy')],
        ];

        return Inertia::render('Public/PrivacyPolicy', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('privacy-policy', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function shippingPolicy()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Delivery Policy', 'url' => url('/shipping-policy')],
        ];

        return Inertia::render('Public/ShippingPolicy', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('shipping-policy', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function returns()
    {
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Refund Policy', 'url' => url('/returns')],
        ];

        return Inertia::render('Public/Returns', [
            'site' => $this->sitePayload(),
            'seo' => $this->seoPayload('returns', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
        ]);
    }

    public function sitemap()
    {
        $sitePayload = $this->sitePayload();
        $crumbs = [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Sitemap', 'url' => url('/sitemap')],
        ];

        $categories = $this->hasCatalogTables()
            ? Category::query()
                ->active()
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'products_count' => $category->products_count,
                    'url' => url('/shop?category='.$category->slug),
                ])
                ->all()
            : [];

        $featuredDishes = $this->hasCatalogTables()
            ? Product::query()
                ->active()
                ->where('is_featured', true)
                ->with('category:id,name,slug')
                ->orderBy('sort_order')
                ->latest()
                ->limit(12)
                ->get()
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'category' => $product->category?->name,
                    'url' => url('/product/'.$product->slug),
                ])
                ->all()
            : [];

        return Inertia::render('Public/Sitemap', [
            'site' => $sitePayload,
            'seo' => $this->seoPayload('sitemap', [
                'jsonLd' => [
                    SeoService::breadcrumbJsonLd($crumbs),
                ],
            ]),
            'categories' => $categories,
            'featuredDishes' => $featuredDishes,
            'xmlSitemapUrl' => url('/sitemap.xml'),
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
