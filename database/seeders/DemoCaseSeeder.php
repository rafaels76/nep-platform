<?php

namespace Database\Seeders;

use App\Enums\CaseStatus;
use App\Models\Company;
use App\Models\OperationalCase;
use App\Orchestration\Interaction;
use App\Orchestration\OrchestrationEngine;
use Illuminate\Database\Seeder;

class DemoCaseSeeder extends Seeder
{
    private array $templates = [
        ['Implementación del SIG ISO 9001', 'sig'],
        ['Auditoría interna ISO 14001', 'sig'],
        ['Plan de seguridad y salud ISO 45001', 'sig'],
        ['Revisión de contratos con proveedores', 'procurement'],
        ['Cotización y compra de equipos críticos', 'procurement'],
        ['Planificación del cronograma de obra', 'proyectos'],
        ['Puesta en marcha de línea de empaque', 'commissioning'],
        ['Verificación de cumplimiento normativo', 'legal'],
        ['Ingeniería básica de ampliación de planta', 'ingenieria'],
        ['Levantamiento de procedimientos operativos', 'general'],
    ];

    public function run(OrchestrationEngine $engine): void
    {
        // [estado final, ¿asignado?]
        $plan = [
            [CaseStatus::Nuevo, false],
            [CaseStatus::Nuevo, true],
            [CaseStatus::EnAnalisis, true],
            [CaseStatus::EnAnalisis, true],
            [CaseStatus::EnEjecucion, true],
            [CaseStatus::EnEjecucion, true],
            [CaseStatus::Cerrado, true],
        ];

        $offset = 0;

        // El Motor rechaza empresas no activas, así que solo se siembran las activas
        foreach (Company::where('status', 'active')->orderBy('tax_id')->get() as $company) {
            $offset++;

            $alreadySeeded = OperationalCase::where('company_id', $company->id)
                ->where('metadata->seed', true)
                ->exists();

            if ($alreadySeeded) {
                continue;
            }

            $creators = $company->users()->where('users.type', 'company_user')->where('users.status', 'active')->get();
            $specialists = $company->users()->where('users.type', 'specialist')->where('users.status', 'active')->get();

            if ($creators->isEmpty()) {
                continue;
            }

            foreach ($plan as $i => [$target, $assigned]) {
                [$title, $type] = $this->templates[($offset * 3 + $i) % count($this->templates)];

                $case = $engine->handle(new Interaction(
                    companyId: $company->id,
                    userId: $creators[$i % $creators->count()]->id,
                    title: $title,
                    description: "Caso de demostración para {$company->name}.",
                    type: $type,
                    metadata: ['seed' => true],
                ));

                if ($assigned && $specialists->isNotEmpty()) {
                    $engine->assign($case, $specialists[$i % $specialists->count()]);
                }

                foreach ([CaseStatus::EnAnalisis, CaseStatus::EnEjecucion, CaseStatus::Cerrado] as $step) {
                    if ($target === CaseStatus::Nuevo) {
                        break;
                    }

                    $engine->transition($case, $step);

                    if ($step === $target) {
                        break;
                    }
                }

                // Reparte las fechas en las últimas semanas (sin disparar auditoría)
                $created = now()->subDays($offset * 4 + $i * 2)->subHours($i);
                $updated = $created->copy()->addDays(2);

                $case->forceFill([
                    'created_at' => $created,
                    'updated_at' => $updated,
                    'closed_at' => $target === CaseStatus::Cerrado ? $updated : null,
                ])->saveQuietly();
            }
        }
    }
}