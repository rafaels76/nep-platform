<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use HasUlids, SoftDeletes, LogsActivity;

    protected $fillable = ['name', 'legal_name', 'tax_id', 'status', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'legal_name', 'tax_id', 'status'])
            ->logOnlyDirty()
            ->useLogName('company')
            ->dontSubmitEmptyLogs();
    }
}