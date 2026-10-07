<?php

namespace App\Models;

use App\Support\ImageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo_path',
        'profile_photo_url',
        'profile_photo_cloudinary_public_id',
        'profile_photo_is_uploaded_to_cloudinary',
        'profile_photo_uploaded_image_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'profile_photo_is_uploaded_to_cloudinary' => 'boolean',
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function profilePhotoUrl(): ?string
    {
        return ImageUrl::resolve($this->profile_photo_url ?: $this->profile_photo_path);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeJoinedDate(Builder $query, ?string $date): void
    {
        $now = now();

        match ($date) {
            'today' => $query->whereDate('created_at', $now->toDateString()),
            'this_week' => $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year),
            'this_year' => $query->whereYear('created_at', $now->year),
            default => null,
        };
    }
}
