<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action',
        'target',
        'ip_address',
        'details',
        'timestamp',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record($action, $target, $details = null)
    {
        return self::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target' => $target,
            'ip_address' => request()->ip(),
            'details' => $details,
            'timestamp' => now()
        ]);
    }
}
