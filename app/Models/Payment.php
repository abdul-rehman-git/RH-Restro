<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'amount',
        'method',
        'status',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pending',
            'paid'     => 'Paid',
            'refunded' => 'Refunded',
            default    => ucfirst($this->status),
        };
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
