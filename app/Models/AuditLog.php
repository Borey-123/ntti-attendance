<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'description',
        'ip_address',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $table = $this->belongsTo(User::class);
    }

    public static function log(string $action, ?string $description = null, ?array $changes = null): self
    {
        $user = auth()->user();
        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'changes' => $changes,
        ]);
    }
}
