<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'genre', 'year', 'director', 'duration', 'rating',
        'description', 'poster_icon', 'poster_path',
        'price_per_day', 'price_per_screening', 'price_per_week',
        'status', 'copies', 'available_copies', 'added_by',
    ];

    protected $casts = [
        'price_per_day'       => 'decimal:2',
        'price_per_screening' => 'decimal:2',
        'price_per_week'      => 'decimal:2',
        'year'                => 'integer',
        'duration'            => 'integer',
        'copies'              => 'integer',
        'available_copies'    => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals()
    {
        return $this->hasMany(Rental::class)->whereIn('status', ['active', 'overdue']);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0)->where('status', '!=', 'inactive');
    }

    public function scopeByGenre($query, $genre)
    {
        return $genre ? $query->where('genre', $genre) : $query;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function hasActiveRental(): bool
    {
        return $this->activeRentals()->exists();
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0 && $this->status !== 'inactive';
    }

    /**
     * Decrement available copies and update status when a copy is rented.
     */
    public function rentOneCopy(): void
    {
        $newAvailable = max(0, $this->available_copies - 1);
        $this->update([
            'available_copies' => $newAvailable,
            'status'           => $newAvailable === 0 ? 'rented' : 'available',
        ]);
    }

    /**
     * Increment available copies and update status when a copy is returned.
     */
    public function returnOneCopy(): void
    {
        $newAvailable = min($this->copies, $this->available_copies + 1);
        $this->update([
            'available_copies' => $newAvailable,
            'status'           => 'available',
        ]);
    }

    /**
     * Auto-derive price_per_screening and price_per_week if not set.
     */
    public function getEffectiveScreeningPriceAttribute(): float
    {
        // If explicitly set, use it; otherwise ~1.5x of one day
        return $this->price_per_screening > 0
            ? (float) $this->price_per_screening
            : round($this->price_per_day * 1.5, 2);
    }

    public function getEffectiveWeeklyPriceAttribute(): float
    {
        // If explicitly set, use it; otherwise 5x daily (discount for week)
        return $this->price_per_week > 0
            ? (float) $this->price_per_week
            : round($this->price_per_day * 5, 2);
    }

    public function getDisplayPosterAttribute(): string
    {
        return $this->poster_path
            ? asset('storage/' . $this->poster_path)
            : $this->poster_icon;
    }
}