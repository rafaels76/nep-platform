<?php

namespace App\Orchestration;

final class Interaction
{
    public function __construct(
        public readonly string $companyId,
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly string $type = 'general',
        public readonly string $channel = 'web',
        public readonly array $metadata = [],
    ) {}
}