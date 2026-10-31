<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DemoUserSeeder extends Seeder
{
    private string $passwordHash;

    public function run(): void
    {
        $this->passwordHash = Hash::make('password123');
        $companies = Company::all()->keyBy('tax_id');
        $registrar = app(PermissionRegistrar::class);

        // Administrador adicional (rol global, team_id = 0)
        $admin = $this->makeUser('Admin Soporte', 'admin2@nep.test', 'ecosystem_admin');
        $admin->companies()->syncWithoutDetaching([$companies['TEST-0001']->id => ['role' => 'owner']]);
        $registrar->setPermissionsTeamId(0);
        $admin->unsetRelation('roles')->unsetRelation('permissions');
        $admin->assignRole('admin');

        // Usuarios de empresa: [nombre, correo, deshabilitado]
        $companyUsers = [
            'TEST-0001' => [['Carla Méndez', 'carla.mendez@nep.test'], ['Luis Paredes', 'luis.paredes@nep.test']],
            'TEST-0002' => [['María Salas', 'maria.salas@nep.test'], ['Jorge Bellorín', 'jorge.bellorin@nep.test']],
            'TEST-0003' => [['Pedro Alcántara', 'pedro.alcantara@nep.test'], ['Gabriela Núñez', 'gabriela.nunez@nep.test', true]],
            'TEST-0004' => [['Andrés Quintero', 'andres.quintero@nep.test'], ['Verónica Lozada', 'veronica.lozada@nep.test']],
            'TEST-0005' => [['Daniel Rojas', 'daniel.rojas@nep.test'], ['Sofía Márquez', 'sofia.marquez@nep.test']],
            'TEST-0006' => [['Ricardo Ferrer', 'ricardo.ferrer@nep.test'], ['Isabel Contreras', 'isabel.contreras@nep.test']],
            'TEST-0007' => [['Héctor Villalba', 'hector.villalba@nep.test'], ['Patricia Gil', 'patricia.gil@nep.test']],
        ];

        foreach ($companyUsers as $taxId => $users) {
            foreach ($users as $data) {
                $user = $this->makeUser($data[0], $data[1], 'company_user', $data[2] ?? false);
                $this->attach($user, $companies[$taxId], 'empresa');
            }
        }

        // Freelancers: [nombre, correo, deshabilitado, [[empresa, rol], ...]]
        $freelancers = [
            ['Ana Rivas', 'ana.rivas@nep.test', false, [['TEST-0001', 'freelancer'], ['TEST-0003', 'freelancer-senior']]],
            ['Bruno Castillo', 'bruno.castillo@nep.test', false, [['TEST-0002', 'freelancer'], ['TEST-0004', 'freelancer-senior'], ['TEST-0005', 'freelancer']]],
            ['Camila Duarte', 'camila.duarte@nep.test', false, [['TEST-0001', 'freelancer-senior'], ['TEST-0002', 'freelancer-senior']]],
            ['Diego Peña', 'diego.pena@nep.test', false, [['TEST-0003', 'freelancer'], ['TEST-0006', 'freelancer']]],
            ['Elena Mora', 'elena.mora@nep.test', false, [['TEST-0004', 'freelancer'], ['TEST-0005', 'freelancer-senior'], ['TEST-0006', 'freelancer-senior']]],
            ['Fabián Linares', 'fabian.linares@nep.test', false, [['TEST-0001', 'freelancer'], ['TEST-0005', 'freelancer']]],
            ['Greta Solano', 'greta.solano@nep.test', true, [['TEST-0002', 'freelancer'], ['TEST-0003', 'freelancer']]],
            ['Hugo Barrios', 'hugo.barrios@nep.test', false, [['TEST-0004', 'freelancer-senior'], ['TEST-0006', 'freelancer'], ['TEST-0007', 'freelancer']]],
        ];

        foreach ($freelancers as [$name, $email, $disabled, $assignments]) {
            $user = $this->makeUser($name, $email, 'specialist', $disabled);

            foreach ($assignments as [$taxId, $role]) {
                $this->attach($user, $companies[$taxId], $role);
            }
        }
    }

    private function makeUser(string $name, string $email, string $type, bool $disabled = false): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->forceFill([
                'name' => $name,
                'password' => $this->passwordHash,
                'type' => $type,
                'status' => $disabled ? 'disabled' : 'active',
                'email_verified_at' => now(),
            ])->save();
        }

        return $user;
    }

    private function attach(User $user, Company $company, string $role): void
    {
        $user->companies()->syncWithoutDetaching([$company->id => ['role' => $role]]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole($role);
    }
}