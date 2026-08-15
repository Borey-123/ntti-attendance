<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'name_kh', 'description', 'head_id'];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($department) {
            if ($department->isDirty('name')) {
                $oldName = $department->getOriginal('name');
                $newName = $department->name;
                Teacher::where('department', $oldName)->update(['department' => $newName]);
            }
        });

        static::deleting(function ($department) {
            Teacher::where('department', $department->name)->update(['department' => '']);
        });
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'department', 'name');
    }
}
