<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador Farmart',
                'email' => 'admin@farmart.test',
                'password' => 'Farmart123!',
                'role' => 'admin',
            ],
            [
                'name' => 'Auxiliar de Farmacia',
                'email' => 'auxiliar1@farmart.test',
                'password' => 'Farmart123!',
                'role' => 'auxiliar_farmacia',
            ],
            [
                'name' => 'Regente de Farmacia',
                'email' => 'regente@farmart.test',
                'password' => 'Farmart123!',
                'role' => 'regente_farmacia',
            ],
            [
                'name' => 'Médico Farmart',
                'email' => 'medico@farmart.test',
                'password' => 'Farmart123!',
                'role' => 'medico',
            ],
            [
                'name' => 'Auditor Farmart',
                'email' => 'auditor@farmart.test',
                'password' => 'Farmart123!',
                'role' => 'auditor',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role_id' => $role->id,
                ]
            );
        }
    }
}
