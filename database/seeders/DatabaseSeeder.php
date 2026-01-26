<?php

namespace Database\Seeders;

use App\Models\PointSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@sivmi.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'phone' => '628000000000',
            ]
        );

        $defaults = [
            'closing' => 50,
            'lead' => 10,
            'share' => 5,
            'save' => 3,
            'comment' => 2,
            'like' => 1,
            'reach' => 0.001,
            'consistency_bonus' => 100,
        ];

        foreach ($defaults as $metric => $value) {
            PointSetting::updateOrCreate(
                ['metric_name' => $metric],
                ['point_value' => $value]
            );
        }

        $this->call(TeamMembersSeeder::class);
        $this->call(SchoolSeeder::class);
        $this->call(AcademicFinanceSeeder::class);
    }
}
