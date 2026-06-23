<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ─── Role Helpers ──────────────────────────────────────────────────
    public function isAdmin(): bool  { return $this->role === 'admin'; }
    public function isStaff(): bool  { return $this->role === 'staff'; }
    public function isUser(): bool   { return $this->role === 'user'; }

    // ─── Relationships ─────────────────────────────────────────────────
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function processedRentals()
    {
        return $this->hasMany(Rental::class, 'processed_by');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'requested_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}