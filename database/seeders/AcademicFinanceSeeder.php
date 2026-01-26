<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Bagian Akademik',
                'email' => 'akademik@sivmi.test',
                'role' => 'akademik',
                'password' => 'password',
            ],
            [
                'name' => 'Bagian Keuangan',
                'email' => 'keuangan@sivmi.test',
                'role' => 'keuangan',
                'password' => 'password',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make($user['password']),
                ]
            );
        }
    }
}
