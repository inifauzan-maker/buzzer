<?php

namespace Database\Seeders;

use App\Models\PointSetting;
use App\Models\Team;
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
        $teamAlpha = Team::updateOrCreate(
            ['team_name' => 'Tim Alpha'],
            ['reminder_phone' => '628111111111']
        );
        $teamBravo = Team::updateOrCreate(
            ['team_name' => 'Tim Bravo'],
            ['reminder_phone' => '628222222222']
        );

        User::firstOrCreate(
            ['email' => 'admin@sivmi.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'phone' => '628000000000',
            ]
        );

        User::firstOrCreate(
            ['email' => 'leader@sivmi.test'],
            [
                'name' => 'Team Leader',
                'password' => Hash::make('password'),
                'role' => 'leader',
                'team_id' => $teamAlpha->id,
                'phone' => '628333333333',
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@sivmi.test'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'team_id' => $teamAlpha->id,
                'phone' => '628444444444',
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
    }
}
