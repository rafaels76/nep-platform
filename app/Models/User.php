<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;


#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    /**
     * Empresas entre las que el usuario puede cambiar:
     * el admin ve todas; el resto, solo a las que está vinculado.
     */
    public function availableCompanies(): Builder|BelongsToMany
    {
        return $this->isAdmin() ? Company::query() : $this->companies();
    }
    /**
     * Empresa activa en sesión, validada contra las permitidas.
     */
    public function activeCompany(): ?Company
    {
        $companyId = session('active_company_id');

        if (! $companyId) {
            return null;
        }

        return $this->availableCompanies()->whereKey($companyId)->first();
    }
    /**
     * Empresa por defecto: la primera vinculada (por nombre);
     * si es admin sin vínculos, la primera del sistema.
     */
    public function defaultCompany(): ?Company
    {
        return $this->companies()->orderBy('companies.name')->first()
            ?? ($this->isAdmin() ? Company::orderBy('name')->first() : null);
    }
    public function isAdmin(): bool
    {
        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId(0);
        $this->unsetRelation('roles')->unsetRelation('permissions');
        $result = $this->hasRole('admin');

        $registrar->setPermissionsTeamId($originalTeamId);
        $this->unsetRelation('roles')->unsetRelation('permissions');

        return $result;
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'type'])
            ->logOnlyDirty()
            ->useLogName('user')
            ->dontSubmitEmptyLogs();
    }
    public function isDisabled(): bool
    {
        return $this->status === 'disabled';
    }
}
