<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'reviewer_name',
        'rating',
        'comment',
        'gallery_images',
        'gallery_image_files',
        'reviewed_at',
        'helpful_count',
        'is_featured',
        'is_approved',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'gallery_images' => 'array',
        'gallery_image_files' => 'array',
        'reviewed_at' => 'date',
        'helpful_count' => 'integer',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('reviewer_name', 'like', "%{$search}%")
                ->orWhere('comment', 'like', "%{$search}%")
                ->orWhereHas('product', function (Builder $productQuery) use ($search): void {
                    $productQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
        });
    }

    public function scopeApprovalStatus(Builder $query, ?string $status): void
    {
        match ($status) {
            'approved' => $query->where('is_approved', true),
            'pending' => $query->where('is_approved', false),
            default => null,
        };
    }

    public function scopeFeaturedStatus(Builder $query, ?string $featured): void
    {
        match ($featured) {
            'featured' => $query->where('is_featured', true),
            'regular' => $query->where('is_featured', false),
            default => null,
        };
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    /**
     * @return array<int, string>
     */
    public function imagePaths(): array
    {
        return ImageUrl::resolveMany(array_values(array_filter($this->gallery_images ?? [])));
    }

    /**
     * @return array<int, array{url: string, file_id: string|null}>
     */
    public function imageFiles(): array
    {
        $files = $this->gallery_image_files ?? [];

        if (is_array($files) && $files !== []) {
            return collect($files)
                ->filter(fn (mixed $file): bool => is_array($file))
                ->map(fn (array $file): array => [
                    'url' => (string) ImageUrl::resolve($file['url'] ?? null),
                    'file_id' => $file['file_id'] ?? null,
                ])
                ->filter(fn (array $file): bool => $file['url'] !== '')
                ->values()
                ->all();
        }

        return collect($this->imagePaths())
            ->map(fn (string $url): array => [
                'url' => $url,
                'file_id' => null,
            ])
            ->all();
    }
}
