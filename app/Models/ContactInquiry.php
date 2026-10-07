<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    public const STATUS_OPTIONS = [
        ['value' => 'new', 'label' => 'New'],
        ['value' => 'in_progress', 'label' => 'In Progress'],
        ['value' => 'replied', 'label' => 'Replied'],
        ['value' => 'closed', 'label' => 'Closed'],
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'source_page',
        'status',
        'admin_notes',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        if (filled($status)) {
            $query->where('status', $status);
        }
    }
}
