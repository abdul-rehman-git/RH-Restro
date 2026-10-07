<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedImage extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'url',
        'original_name',
        'mime_type',
        'size',
        'cloudinary_public_id',
        'is_uploaded_to_cloudinary',
    ];

    protected $casts = [
        'is_uploaded_to_cloudinary' => 'boolean',
        'size' => 'integer',
    ];
}
