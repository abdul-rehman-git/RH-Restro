<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'image_cloudinary_public_id',
        'image_is_uploaded_to_cloudinary',
        'image_uploaded_image_id',
        'banner_image',
        'banner_image_cloudinary_public_id',
        'banner_image_is_uploaded_to_cloudinary',
        'banner_image_uploaded_image_id',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'image_is_uploaded_to_cloudinary' => 'boolean',
        'banner_image_is_uploaded_to_cloudinary' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
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

    public function scopeSortForAdmin(Builder $query, ?string $sortBy, ?string $direction): void
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['sort_order', 'name', 'updated_at'], true)) {
            $query->orderBy($sortBy, $direction);

            return;
        }

        $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function imageUrl(): ?string
    {
        return ImageUrl::resolve($this->image);
    }

    public function bannerImageUrl(): ?string
    {
        return ImageUrl::resolve($this->banner_image);
    }
}
