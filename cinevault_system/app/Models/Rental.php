<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id', 'user_id', 'customer_name', 'customer_contact',
        'rental_date', 'due_date', 'returned_date',
        'rental_type',          // 'screening' | 'days' | 'weeks'
        'days',
        'price_per_day',        // kept for legacy / days type
        'price_per_screening',  // for screening type
        'price_base',           // base unit price used
        'total_amount', 'payment_method',
        'payment_reference', 'status', 'processed_by', 'notes',
    ];

    protected $casts = [
        'rental_date'         => 'date',
        'due_date'            => 'date',
        'returned_date'       => 'date',
        'price_per_day'       => 'decimal:2',
        'price_per_screening' => 'decimal:2',
        'price_base'          => 'decimal:2',
        'total_amount'        => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->due_date->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'active') return 0;
        return max(0, now()->startOfDay()->diffInDays($this->due_date, false));
    }

    public function getRentalTypeLabel(): string
    {
        return match($this->rental_type) {
            'screening' => 'One-Time Screening',
            'weeks'     => 'Weekly Rental',
            default     => 'Daily Rental',
        };
    }

    public function getDurationLabel(): string
    {
        return match($this->rental_type) {
            'screening' => 'Single screening',
            'weeks'     => $this->days / 7 . ' week(s)',
            default     => $this->days . ' day(s)',
        };
    }
}