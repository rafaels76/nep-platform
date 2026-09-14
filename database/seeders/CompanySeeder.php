<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['tax_id' => 'TEST-0001'], // clave única para que sea idempotente
            [
                'name' => 'NEP Empresa Demo',
                'legal_name' => 'NEP Demo Legal S.A.',
                'status' => 'active',
                'metadata' => ['seed' => true],
            ]
        );
    }
}