<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// Relations are resolved at runtime via Eloquent; no explicit imports needed for model classes.

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'total_amount',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'shipped'   => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
        });
    }

    public function scopeFilterStatus(Builder $query, ?string $status): void
    {
        if (blank($status)) {
            return;
        }
        $query->where('status', $status);
    }

    public function scopeFilterDate(Builder $query, ?string $date): void
    {
        $now = now();
        match ($date) {
            'today'      => $query->whereDate('created_at', $now->toDateString()),
            'this_week'  => $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year),
            'this_year'  => $query->whereYear('created_at', $now->year),
            default      => null,
        };
    }
}
