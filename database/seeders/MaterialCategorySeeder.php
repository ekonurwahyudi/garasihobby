<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'OLI', 'name' => 'Oli & Fluida'],
            ['code' => 'FLT', 'name' => 'Filter'],
            ['code' => 'MSN', 'name' => 'Mesin'],
            ['code' => 'KAK', 'name' => 'Kaki-kaki'],
            ['code' => 'REM', 'name' => 'Sistem Rem'],
            ['code' => 'ELK', 'name' => 'Kelistrikan'],
            ['code' => 'CHM', 'name' => 'Chemical'],
            ['code' => 'FMP', 'name' => 'Fast Moving Part'],
        ];

        foreach ($categories as $cat) {
            MaterialCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
