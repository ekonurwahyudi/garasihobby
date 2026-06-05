<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ChecklistCategorySeeder::class,
            ChecklistItemSeeder::class,
            MaterialCategorySeeder::class,
            AssetCategorySeeder::class,
            DebtReceivableCategorySeeder::class,
        ]);
    }
}
