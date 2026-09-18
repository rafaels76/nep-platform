<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@nep.test'],
            [
                'name' => 'Admin NEP',
                'password' => Hash::make('password123'), // solo para entorno local/dev
                'type' => 'ecosystem_admin',
                'email_verified_at' => now(),
            ]
        );
        $company = Company::where('tax_id', 'TEST-0001')->first();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(0);
        $user->assignRole('admin');

        if ($company) {
            $user->companies()->syncWithoutDetaching([
                $company->id => ['role' => 'owner'],
            ]);
        }
    }
}