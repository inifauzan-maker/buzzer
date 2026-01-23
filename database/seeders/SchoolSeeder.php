<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        if (School::query()->exists()) {
            return;
        }

        $jsonPath = base_path('pendaftaran/database/schools.json');
        $csvPath = base_path('pendaftaran/database/sekolahVM.csv');
        $rows = [];

        if (is_file($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true) ?? [];
            foreach ($data as $item) {
                $name = trim($item['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $rows[] = [
                    'name' => $name,
                    'type' => $item['type'] ?? null,
                    'city' => $item['city'] ?? null,
                    'province' => $item['province'] ?? null,
                    'level_group' => $item['level_group'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } elseif (is_file($csvPath)) {
            $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $name = trim($line);
                if ($name === '') {
                    continue;
                }

                $rows[] = [
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            School::query()->insert($chunk);
        }
    }
}
