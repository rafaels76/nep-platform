<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['TEST-0003', 'Constructora Andina', 'Constructora Andina C.A.', 'active'],
            ['TEST-0004', 'Petroservicios del Sur', 'Petroservicios del Sur S.A.', 'active'],
            ['TEST-0005', 'Logística Caribe', 'Logística Caribe LLC', 'active'],
            ['TEST-0006', 'Alimentos La Meseta', 'Alimentos La Meseta C.A.', 'active'],
            ['TEST-0007', 'Manufacturas Orinoco', 'Manufacturas Orinoco C.A.', 'suspended'],
        ];

        foreach ($companies as [$taxId, $name, $legalName, $status]) {
            Company::firstOrCreate(
                ['tax_id' => $taxId],
                [
                    'name' => $name,
                    'legal_name' => $legalName,
                    'status' => $status,
                    'metadata' => ['seed' => true],
                ]
            );
        }
    }
}