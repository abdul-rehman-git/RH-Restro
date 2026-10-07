<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Categories\GetCategoryOptionsAction;
use App\Actions\Products\DeleteProductAction;
use App\Actions\Products\SaveProductAction;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Pipelines\FilterByColumns;
use App\Pipelines\SearchByColumns;
use App\Pipelines\SortByColumn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(
        Request $request,
        GetCategoryOptionsAction $getCategoryOptions,
    ): Response {
        return Inertia::render('Products/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'featured' => $request->string('featured')->toString(),
                'category_id' => $request->string('category_id')->toString(),
                'min_price' => $request->string('min_price')->toString(),
                'max_price' => $request->string('max_price')->toString(),
                'sort_by' => $request->string('sort_by')->toString(),
                'sort_direction' => $request->string('sort_direction')->toString(),
            ],
            'categories' => $getCategoryOptions->handle(),
            'products' => Product::query()
                ->with('category:id,name')
                ->search($request->string('search')->toString())
                ->status($request->string('status')->toString())
                ->featuredFilter($request->string('featured')->toString())
                ->category($request->input('category_id'))
                ->priceBetween($request->input('min_price'), $request->input('max_price'))
                ->sortForAdmin(
                    $request->string('sort_by')->toString(),
                    $request->string('sort_direction')->toString(),
                )
                ->paginate(10)
                ->withQueryString()
                ->through(fn(Product $product): array => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'image_url' => $product->imageUrl(),
                    'image_count' => count($product->imagePaths()),
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'is_featured' => $product->is_featured,
                    'is_active' => $product->is_active,
                    'badge_label' => $product->badge_label,
                    'category' => $product->category
                        ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                        ]
                        : null,
                    'updated_at' => $product->updated_at?->format('M d, Y'),
                ]),
        ]);
    }

    public function create(GetCategoryOptionsAction $getCategoryOptions): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => $getCategoryOptions->handle(),
        ]);
    }

    public function store(StoreProductRequest $request, SaveProductAction $saveProduct): RedirectResponse
    {
        $saveProduct->handle($request);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product, GetCategoryOptionsAction $getCategoryOptions): Response
    {
        $product->load('variants');

        return Inertia::render('Products/Edit', [
            'product' => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'title' => $product->title,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'dimensions' => $product->dimensions,
                'materials' => $product->materials,
                'feature_points' => $product->feature_points ?? [],
                'care_instructions' => $product->care_instructions,
                'shipping_note' => $product->shipping_note,
                'price' => $product->price,
                'compare_price' => $product->compare_price,
                'badge_label' => $product->badge_label,
                'stock_quantity' => $product->stock_quantity,
                'sort_order' => $product->sort_order,
                'is_featured' => $product->is_featured,
                'is_active' => $product->is_active,
                'images' => collect($product->imagePaths())
                    ->map(fn(string $url): array => [
                        'path' => $url,
                        'url' => $url,
                    ])
                    ->values()
                    ->all(),
                'variants' => $product->variants->map(fn($variant): array => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => (float) $variant->price,
                    'compare_price' => $variant->compare_price ? (float) $variant->compare_price : null,
                    'stock_quantity' => $variant->stock_quantity,
                    'image' => $variant->imageUrl(),
                    'sort_order' => $variant->sort_order,
                    'is_active' => $variant->is_active,
                ])->all(),
            ],
            'categories' => $getCategoryOptions->handle(),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        SaveProductAction $saveProduct,
    ): RedirectResponse {
        $saveProduct->handle($request, $product);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product, DeleteProductAction $deleteProduct): RedirectResponse
    {
        $deleteProduct->handle($product);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
