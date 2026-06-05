<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'TRPL', 'name' => 'TERPAL', 'description' => 'TERPAL'],
            ['code' => 'ALKER', 'name' => 'Alat Kerja', 'description' => 'Alat Kerja'],
            ['code' => 'KOPR', 'name' => 'Kendaraan Operasional', 'description' => 'Kendaraan Operasional'],
            ['code' => 'PEPR', 'name' => 'Peralatan Produksi', 'description' => 'Peralatan Produksi'],
            ['code' => 'MSIN', 'name' => 'Mesin produksi', 'description' => 'Mesin produksi'],
            ['code' => 'BGNN', 'name' => 'Bagunan', 'description' => 'Bagunan'],
            ['code' => 'TNAH', 'name' => 'TANAH', 'description' => 'TANAH'],
        ];

        foreach ($categories as $category) {
            AssetCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
