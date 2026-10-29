<?php

namespace App\Listeners;

use App\Events\CaseCreated;

class RecordOrchestrationDecision
{
    public function handle(CaseCreated $event): void
    {
        activity('orchestration')
            ->performedOn($event->case)
            ->causedBy($event->case->creator)
            ->withProperties([
                'decision' => 'caso_creado',
                'channel' => $event->channel,
                'company_id' => $event->case->company_id,
                'type' => $event->case->type,
            ])
            ->log('Interacción convertida en Caso Operacional');
    }
}