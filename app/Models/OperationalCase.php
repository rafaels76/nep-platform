<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;

class OperationalCase extends Model
{
    use HasUlids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'status',
        'created_by',
        'assigned_to',
        'metadata',
        'closed_at',
    ];

    protected $attributes = [
        'status' => 'nuevo',
        'type' => 'general',
    ];

    protected $casts = [
        'status' => CaseStatus::class,
        'metadata' => 'array',
        'closed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'status', 'assigned_to'])
            ->logOnlyDirty()
            ->useLogName('case')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Casos visibles para un usuario: los de su empresa activa y,
     * si es freelancer, solo los que tiene asignados.
     */
    public function scopeVisibleTo(Builder $query, User $user, ?Company $company = null): Builder
    {
        $company ??= $user->activeCompany();

        if (! $company) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('company_id', $company->id)
            ->when($user->type === 'specialist', fn($q) => $q->where('assigned_to', $user->id));
    }
}
