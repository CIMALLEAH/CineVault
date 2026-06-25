<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'requested_by', 'movie_id', 'payload',
        'reason', 'status', 'reviewed_by', 'admin_note', 'reviewed_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'add_movie'    => 'Add Movie',
            'edit_movie'   => 'Edit Movie',
            'delete_movie' => 'Delete Movie',
            default        => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'add_movie'    => 'badge-green',
            'edit_movie'   => 'badge-blue',
            'delete_movie' => 'badge-red',
            default        => 'badge-gold',
        };
    }
}