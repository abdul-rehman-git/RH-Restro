<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\SaveCategoryAction;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Categories/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'sort_by' => $request->string('sort_by')->toString(),
                'sort_direction' => $request->string('sort_direction')->toString(),
            ],
            'categories' => Category::query()
                ->withCount('products')
                ->search($request->string('search')->toString())
                ->status($request->string('status')->toString())
                ->sortForAdmin(
                    $request->string('sort_by')->toString(),
                    $request->string('sort_direction')->toString(),
                )
                ->paginate(10)
                ->withQueryString()
                ->through(fn(Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->imageUrl(),
                    'banner_image_url' => $category->bannerImageUrl(),
                    'sort_order' => $category->sort_order,
                    'is_active' => $category->is_active,
                    'is_featured' => $category->is_featured,
                    'products_count' => $category->products_count,
                    'updated_at' => $category->updated_at?->format('M d, Y'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Create');
    }

    public function store(StoreCategoryRequest $request, SaveCategoryAction $saveCategory): RedirectResponse
    {
        $saveCategory->handle($request);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'is_featured' => $category->is_featured,
                'image_url' => $category->imageUrl(),
                'banner_image_url' => $category->bannerImageUrl(),
            ],
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        SaveCategoryAction $saveCategory,
    ): RedirectResponse {
        $saveCategory->handle($request, $category);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category, DeleteCategoryAction $deleteCategory): RedirectResponse
    {
        return redirect()
            ->route('categories.index')
            ->with('status', $deleteCategory->handle($category));
    }
}
