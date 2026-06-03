<?php

namespace Database\Seeders;

use App\Models\ChecklistCategory;
use Illuminate\Database\Seeder;

class ChecklistCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'BRK', 'name' => 'Braken System'],
            ['code' => 'ASR', 'name' => 'As Roda'],
            ['code' => 'STB', 'name' => 'Stabilizer'],
            ['code' => 'SUS', 'name' => 'Suspension'],
            ['code' => 'STR', 'name' => 'Steering'],
            ['code' => 'BSH', 'name' => 'Bushing'],
            ['code' => 'EGM', 'name' => 'Engine Mounting'],
            ['code' => 'BAN', 'name' => 'Ban'],
            ['code' => 'IND', 'name' => 'Indikator Speedometer'],
        ];

        foreach ($categories as $cat) {
            ChecklistCategory::withTrashed()->updateOrCreate(
                ['code' => $cat['code']],
                [...$cat, 'deleted_at' => null]
            );
        }
    }
}
