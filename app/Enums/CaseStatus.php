<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Nuevo = 'nuevo';
    case EnAnalisis = 'en_analisis';
    case EnEjecucion = 'en_ejecucion';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Nuevo => 'Nuevo',
            self::EnAnalisis => 'En análisis',
            self::EnEjecucion => 'En ejecución',
            self::Cerrado => 'Cerrado',
        };
    }

    /** Transiciones permitidas desde cada estado. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Nuevo => [self::EnAnalisis, self::Cerrado],
            self::EnAnalisis => [self::EnEjecucion, self::Cerrado],
            self::EnEjecucion => [self::Cerrado],
            self::Cerrado => [],
        };
    }
}