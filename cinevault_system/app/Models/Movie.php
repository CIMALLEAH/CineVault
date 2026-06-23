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
        'description', 'poster_icon', 'poster_path', 'price_per_day',
        'status', 'copies', 'added_by',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'year'          => 'integer',
        'duration'      => 'integer',
        'copies'        => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────────────────
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals()
    {
        return $this->hasMany(Rental::class)->where('status', 'active');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByGenre($query, $genre)
    {
        return $genre ? $query->where('genre', $genre) : $query;
    }

    // ─── Helpers ───────────────────────────────────────────────────────
    public function hasActiveRental(): bool
    {
        return $this->activeRentals()->exists();
    }

    public function getDisplayPosterAttribute(): string
    {
        return $this->poster_path
            ? asset('storage/' . $this->poster_path)
            : $this->poster_icon;
    }
}