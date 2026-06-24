<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'description', 'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function write(
        string $action,
        string $description,
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        array $old = [],
        array $new = []
    ): void {
        static::create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => strtoupper($action),
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'description' => $description,
            'old_values'  => empty($old) ? null : $old,
            'new_values'  => empty($new) ? null : $new,
            'ip_address'  => request()->ip(),
        ]);
    }
}