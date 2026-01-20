<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeamMembersSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = database_path('seeders/data/team-members.php');

        if (! file_exists($dataPath)) {
            Log::warning('TeamMembersSeeder: data file not found.', ['path' => $dataPath]);
            return;
        }

        $teams = require $dataPath;

        foreach ($teams as $teamData) {
            $team = Team::updateOrCreate(
                ['team_name' => $teamData['team_name']],
                ['reminder_phone' => $teamData['reminder_phone'] ?? null]
            );

            $members = $teamData['members'] ?? [];

            foreach ($members as $member) {
                if (($member['role'] ?? '') === 'leader') {
                    $exists = User::where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->where('email', '!=', $member['email'])
                        ->exists();

                    if ($exists) {
                        Log::warning('TeamMembersSeeder: leader already exists for team.', [
                            'team_id' => $team->id,
                            'team_name' => $team->team_name,
                            'email' => $member['email'],
                        ]);
                        continue;
                    }
                }

                User::updateOrCreate(
                    ['email' => $member['email']],
                    [
                        'team_id' => $team->id,
                        'role' => $member['role'],
                        'name' => $member['name'],
                        'phone' => $member['phone'] ?? null,
                        'password' => Hash::make($member['password'] ?? 'password'),
                    ]
                );
            }
        }
    }
}
