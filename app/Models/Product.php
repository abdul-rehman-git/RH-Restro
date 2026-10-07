<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'sku',
        'short_description',
        'description',
        'dimensions',
        'materials',
        'image',
        'image_cloudinary_public_id',
        'image_is_uploaded_to_cloudinary',
        'image_uploaded_image_id',
        'gallery_images',
        'gallery_image_files',
        'feature_points',
        'care_instructions',
        'shipping_note',
        'price',
        'compare_price',
        'badge_label',
        'stock_quantity',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'gallery_images' => 'array',
        'gallery_image_files' => 'array',
        'feature_points' => 'array',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'image_is_uploaded_to_cloudinary' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        match ($status) {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            default => null,
        };
    }

    public function scopeFeaturedFilter(Builder $query, ?string $featured): void
    {
        match ($featured) {
            'featured' => $query->where('is_featured', true),
            'regular' => $query->where('is_featured', false),
            default => null,
        };
    }

    public function scopeCategory(Builder $query, int|string|null $categoryId): void
    {
        if (filled($categoryId)) {
            $query->where('category_id', $categoryId);
        }
    }

    public function scopePriceBetween(Builder $query, mixed $minPrice, mixed $maxPrice): void
    {
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('price', '<=', (float) $maxPrice);
        }
    }

    public function scopeSortForAdmin(Builder $query, ?string $sortBy, ?string $direction): void
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['sort_order', 'id', 'title', 'price', 'stock_quantity', 'updated_at'], true)) {
            $query->orderBy($sortBy, $direction);

            return;
        }

        $query->newestFirst();
    }

    public function scopeNewestFirst(Builder $query): void
    {
        $query->latest()->orderByDesc('id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeWithApprovedReviewStats(Builder $query): void
    {
        $query
            ->withCount([
                'reviews as approved_reviews_count' => fn (Builder $builder) => $builder->approved(),
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn (Builder $builder) => $builder->approved(),
            ], 'rating');
    }

    public function scopeCategorySlug(Builder $query, ?string $slug): void
    {
        if (blank($slug)) {
            return;
        }

        $query->whereHas('category', fn (Builder $builder) => $builder->where('slug', $slug));
    }

    /**
     * @return array<int, string>
     */
    public function imagePaths(): array
    {
        $paths = array_values(array_filter($this->gallery_images ?? []));

        if ($paths !== []) {
            return ImageUrl::resolveMany($paths);
        }

        return $this->image ? [ImageUrl::resolve($this->image)] : [];
    }

    public function imageUrl(): ?string
    {
        $path = $this->imagePaths()[0] ?? null;

        return ImageUrl::resolve($path);
    }

    /**
     * @return array<int, array{
     *     url: string,
     *     file_id: string|null,
     *     cloudinary_public_id: string|null,
     *     is_uploaded_to_cloudinary: bool,
     *     uploaded_image_id: int|null,
     *     local_path: string|null
     * }>
     */
    public function galleryImageFiles(): array
    {
        $files = $this->gallery_image_files ?? [];

        if (is_array($files) && $files !== []) {
            return collect($files)
                ->filter(fn (mixed $file): bool => is_array($file))
                ->map(fn (array $file): array => [
                    'url' => (string) ImageUrl::resolve($file['url'] ?? null),
                    'file_id' => $file['file_id'] ?? null,
                    'cloudinary_public_id' => $file['cloudinary_public_id'] ?? null,
                    'is_uploaded_to_cloudinary' => (bool) ($file['is_uploaded_to_cloudinary'] ?? false),
                    'uploaded_image_id' => isset($file['uploaded_image_id'])
                        ? (int) $file['uploaded_image_id']
                        : null,
                    'local_path' => $file['local_path'] ?? null,
                ])
                ->filter(fn (array $file): bool => $file['url'] !== '')
                ->values()
                ->all();
        }

        return collect($this->imagePaths())
            ->map(fn (string $url): array => [
                'url' => $url,
                'file_id' => null,
                'cloudinary_public_id' => null,
                'is_uploaded_to_cloudinary' => false,
                'uploaded_image_id' => null,
                'local_path' => null,
            ])
            ->all();
    }
}
