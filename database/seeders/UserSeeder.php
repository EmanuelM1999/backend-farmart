<?php

namespace Database\Seeders;

use App\Models\User;
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
            ],
            [
                'name' => 'Auxiliar de Farmacia',
                'email' => 'auxiliar1@farmart.test',
                'password' => 'Farmart123!',
            ],
            [
                'name' => 'Regente de Farmacia',
                'email' => 'regente@farmart.test',
                'password' => 'Farmart123!',
            ],
            [
                'name' => 'Médico Farmart',
                'email' => 'medico@farmart.test',
                'password' => 'Farmart123!',
            ],
            [
                'name' => 'Auditor Farmart',
                'email' => 'auditor@farmart.test',
                'password' => 'Farmart123!',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );
        }
    }
}
