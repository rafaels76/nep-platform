<?php

namespace App\Events;

use App\Models\OperationalCase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OperationalCase $case,
        public string $channel,
    ) {}
}