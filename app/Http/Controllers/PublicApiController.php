<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPayloads;
use App\Http\Requests\PublicApi\StorePublicContactInquiryRequest;
use App\Http\Requests\PublicApi\StorePublicReviewRequest;
use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\ImageUploadService;
use App\Support\SettingStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    use BuildsPublicPayloads;

    public function siteSettings(): JsonResponse
    {
        return response()->json([
            'site' => $this->sitePayload(),
        ]);
    }

    public function home(): JsonResponse
    {
        $home = SettingStore::homePage();

        $categories = Category::query()
            ->active()
            ->featured()
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $products = Product::query()
            ->with('category:id,name,slug')
            ->withApprovedReviewStats()
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->latest()
            ->limit(8)
            ->get();

        $testimonials = Review::query()
            ->approved()
            ->where('is_featured', true)
            ->latest('reviewed_at')
            ->limit(6)
            ->get();

        return response()->json([
            'site' => $this->sitePayload(),
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

    public function about(): JsonResponse
    {
        $about = SettingStore::aboutPage();

        return response()->json([
            'site' => $this->sitePayload(),
            'about' => $this->aboutContentPayload($about),
        ]);
    }

    public function contact(): JsonResponse
    {
        return response()->json([
            'site' => $this->sitePayload(),
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $reviewQuery = Review::query()
            ->with('product:id,title,slug')
            ->approved()
            ->when(
                $request->string('product')->toString(),
                fn ($query, $slug) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('slug', $slug)),
            )
            ->latest('reviewed_at')
            ->latest();

        $reviews = (clone $reviewQuery)->paginate(8);
        $allApproved = (clone $reviewQuery)->get();

        return response()->json([
            'site' => $this->sitePayload(),
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

    public function products(Request $request): JsonResponse
    {
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

        return response()->json([
            'site' => $this->sitePayload(),
            'categories' => $categories->map(fn (Category $category): array => [
                'id' => $category->id,
                'slug' => $category->slug,
                'label' => $category->name,
            ])->all(),
            'products' => [
                'data' => $products->getCollection()->map(fn (Product $product): array => $this->productCardPayload($product))->values()->all(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function product(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with('category:id,name,slug')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $reviewQuery = Review::query()
            ->approved()
            ->where('product_id', $product->id)
            ->latest('reviewed_at');

        $reviews = (clone $reviewQuery)->limit(6)->get();
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

        return response()->json([
            'site' => $this->sitePayload(),
            'product' => [
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
            ],
            'reviews' => $reviews->map(fn (Review $review): array => [
                'id' => $review->id,
                'name' => $review->reviewer_name,
                'rating' => $review->rating,
                'date' => $review->reviewed_at?->format('F d, Y'),
                'comment' => $review->comment,
                'images' => $this->reviewImageUrls($review),
                'verified' => $review->is_verified_purchase,
            ])->all(),
            'relatedProducts' => $relatedProducts->map(fn (Product $relatedProduct): array => $this->productCardPayload($relatedProduct))->all(),
        ]);
    }

    public function storeReview(StorePublicReviewRequest $request, ImageUploadService $uploads): JsonResponse
    {
        $images = $uploads->uploadMany($request->imageFiles(), 'images', 'public-review');

        Review::query()->create([
            ...$request->validated(),
            'gallery_images' => $images !== [] ? array_column($images, 'url') : null,
            'gallery_image_files' => $images !== [] ? $images : null,
            'reviewed_at' => now()->toDateString(),
            'helpful_count' => 0,
            'is_featured' => false,
            'is_approved' => false,
            'is_verified_purchase' => false,
        ]);

        $uploads->queueCloudinaryUploads($images);

        return response()->json([
            'message' => 'Your review has been submitted and is now pending approval.',
        ]);
    }

    public function storeContactInquiry(StorePublicContactInquiryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        ContactInquiry::query()->create([
            ...$validated,
            'source_page' => $validated['source_page'] ?? 'contact',
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Your message has been sent successfully.',
        ]);
    }

    public function trackOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:100'],
        ]);

        $normalizedOrderNumber = strtoupper(trim((string) $validated['order_number']));

        $order = Order::query()
            ->with(['orderItems', 'payment'])
            ->whereRaw('UPPER(order_number) = ?', [$normalizedOrderNumber])
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'No order found for this order number. Please verify and try again.',
            ], 404);
        }

        return response()->json([
            'message' => 'Order found.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total_amount' => (float) $order->total_amount,
                'placed_at' => $order->created_at?->format('M d, Y h:i A'),
                'updated_at' => $order->updated_at?->format('M d, Y h:i A'),
                'notes' => $order->notes,
                'timeline' => $this->statusTimeline($order->status),
                'items' => $order->orderItems->map(fn ($item): array => [
                    'id' => $item->id,
                    'title' => $item->product_title,
                    'price' => (float) $item->product_price,
                    'quantity' => (int) $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ])->values()->all(),
                'payment' => $order->payment ? [
                    'method' => $order->payment->method,
                    'status' => $order->payment->status,
                    'status_label' => $order->payment->status_label,
                    'amount' => (float) $order->payment->amount,
                    'created_at' => $order->payment->created_at?->format('M d, Y h:i A'),
                ] : null,
            ],
        ]);
    }

    /**
     * @return array<int, array{key: string, label: string, completed: bool, current: bool}>
     */
    private function statusTimeline(string $currentStatus): array
    {
        $steps = [
            ['key' => 'pending', 'label' => 'Order Placed'],
            ['key' => 'confirmed', 'label' => 'Order Confirmed'],
            ['key' => 'shipped', 'label' => 'Shipped'],
            ['key' => 'delivered', 'label' => 'Delivered'],
        ];

        if ($currentStatus === 'cancelled') {
            return [
                ['key' => 'pending', 'label' => 'Order Placed', 'completed' => true, 'current' => false],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'completed' => true, 'current' => true],
            ];
        }

        $currentStepIndex = collect($steps)->search(fn (array $step): bool => $step['key'] === $currentStatus);

        return collect($steps)
            ->map(function (array $step, int $index) use ($currentStepIndex): array {
                $completed = is_int($currentStepIndex) ? $index <= $currentStepIndex : false;
                $current = is_int($currentStepIndex) ? $index === $currentStepIndex : false;

                return [
                    ...$step,
                    'completed' => $completed,
                    'current' => $current,
                ];
            })
            ->values()
            ->all();
    }

}
