<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_price',
        'stock_quantity',
        'image',
        'image_cloudinary_public_id',
        'image_is_uploaded_to_cloudinary',
        'image_uploaded_image_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'image_is_uploaded_to_cloudinary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imageUrl(): ?string
    {
        return ImageUrl::resolve($this->image);
    }
}
