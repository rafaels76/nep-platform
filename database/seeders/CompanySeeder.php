<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['tax_id' => 'TEST-0001'],
            [
                'name' => 'NEP Empresa Demo',
                'legal_name' => 'NEP Demo Legal S.A.',
                'status' => 'active',
                'metadata' => ['seed' => true],
            ]
        );

        Company::firstOrCreate(
            ['tax_id' => 'TEST-0002'],
            [
                'name' => 'NEP Empresa Demo 2',
                'legal_name' => 'NEP Demo Legal 2 S.A.',
                'status' => 'active',
                'metadata' => ['seed' => true],
            ]
        );
    }
}
