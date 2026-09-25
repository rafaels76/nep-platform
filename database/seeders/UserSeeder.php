<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        $company1 = Company::where('tax_id', 'TEST-0001')->first();
        $company2 = Company::where('tax_id', 'TEST-0002')->first();

        // Administrador (acceso global)
        $admin = User::firstOrCreate(
            ['email' => 'admin@nep.test'],
            [
                'name' => 'Admin NEP',
                'password' => Hash::make('password123'),
                'type' => 'ecosystem_admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->companies()->syncWithoutDetaching([$company1->id => ['role' => 'owner']]);
        $registrar->setPermissionsTeamId(0);
        $admin->assignRole('admin');

        // Usuario de empresa
        $empresaUser = User::firstOrCreate(
            ['email' => 'empresa@nep.test'],
            [
                'name' => 'Usuario Empresa Demo',
                'password' => Hash::make('password123'),
                'type' => 'company_user',
                'email_verified_at' => now(),
            ]
        );
        $empresaUser->companies()->syncWithoutDetaching([$company1->id => ['role' => 'empresa']]);
        $registrar->setPermissionsTeamId($company1->id);
        $empresaUser->assignRole('empresa');

        // Freelancer con roles distintos según la empresa
        $freelancer = User::firstOrCreate(
            ['email' => 'freelancer@nep.test'],
            [
                'name' => 'Freelancer Demo',
                'password' => Hash::make('password123'),
                'type' => 'specialist',
                'email_verified_at' => now(),
            ]
        );

        $freelancer->companies()->syncWithoutDetaching([
            $company1->id => ['role' => 'freelancer'],
            $company2->id => ['role' => 'freelancer-senior'],
        ]);

        $registrar->setPermissionsTeamId($company1->id);
        $freelancer->assignRole('freelancer');

        $registrar->setPermissionsTeamId($company2->id);
        $freelancer->assignRole('freelancer-senior');
    }
}