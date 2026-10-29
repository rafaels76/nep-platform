<?php

namespace App\Orchestration;

use App\Enums\CaseStatus;
use App\Events\CaseCreated;
use App\Models\Company;
use App\Models\OperationalCase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrchestrationEngine
{
    /**
     * Punto de entrada único: convierte una interacción en un Caso Operacional.
     */
    public function handle(Interaction $interaction): OperationalCase
    {
        $company = Company::findOrFail($interaction->companyId);
        $user = User::findOrFail($interaction->userId);

        $this->assertCanOperate($user, $company);

        $case = DB::transaction(fn () => OperationalCase::create([
            'company_id' => $company->id,
            'title' => $interaction->title,
            'description' => $interaction->description,
            'type' => $interaction->type,
            'created_by' => $user->id,
            'metadata' => array_merge($interaction->metadata, [
                'channel' => $interaction->channel,
            ]),
        ]));

        CaseCreated::dispatch($case, $interaction->channel);

        return $case;
    }

    /**
     * Asigna el Caso a un especialista vinculado a la empresa del Caso.
     */
    public function assign(OperationalCase $case, User $specialist): OperationalCase
    {
        if ($specialist->type !== 'specialist' || $specialist->isDisabled()) {
            throw ValidationException::withMessages([
                'assigned_to' => 'Solo se puede asignar a un freelancer activo.',
            ]);
        }

        $linked = $case->company->users()->whereKey($specialist->id)->exists();

        if (! $linked) {
            throw ValidationException::withMessages([
                'assigned_to' => 'El freelancer no está vinculado a la empresa de este caso.',
            ]);
        }

        $case->update(['assigned_to' => $specialist->id]);

        return $case;
    }

    /**
     * Cambia el estado respetando las transiciones permitidas por el enum.
     */
    public function transition(OperationalCase $case, CaseStatus $to): OperationalCase
    {
        if (! in_array($to, $case->status->allowedTransitions(), true)) {
            throw ValidationException::withMessages([
                'status' => "No se puede pasar de {$case->status->label()} a {$to->label()}.",
            ]);
        }

        $case->status = $to;

        if ($to === CaseStatus::Cerrado) {
            $case->closed_at = now();
        }

        $case->save();

        return $case;
    }

    protected function assertCanOperate(User $user, Company $company): void
    {
        if ($user->isDisabled()) {
            throw new AuthorizationException('El usuario está deshabilitado.');
        }

        if ($company->status !== 'active') {
            throw new AuthorizationException('La empresa no está activa.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if (! $company->users()->whereKey($user->id)->exists()) {
            throw new AuthorizationException('El usuario no pertenece a esta empresa.');
        }
    }
}