<?php

namespace Database\Seeders;

use App\Models\DebtReceivableCategory;
use Illuminate\Database\Seeder;

class DebtReceivableCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'PIOW', 'name' => 'PIUTANG OWNER', 'type' => 'receivable', 'description' => 'PIUTANG OWNER'],
            ['code' => 'HUBA', 'name' => 'Hutang Bank', 'type' => 'debt', 'description' => 'Hutang Bank'],
            ['code' => 'HULE', 'name' => 'Hutang Leasing', 'type' => 'debt', 'description' => 'Hutang Leasing'],
            ['code' => 'HUPA', 'name' => 'Hutang Pajak', 'type' => 'debt', 'description' => 'Hutang Pajak'],
            ['code' => 'HUGA', 'name' => 'Hutang Gaji', 'type' => 'debt', 'description' => 'Hutang Gaji'],
            ['code' => 'HUPB', 'name' => 'Hutang Pembelian Bahan Baku', 'type' => 'debt', 'description' => 'Hutang Pembelian Bahan Baku'],
            ['code' => 'HUUS', 'name' => 'Hutang Usaha', 'type' => 'debt', 'description' => 'Hutang Usaha'],
            ['code' => 'PIUS', 'name' => 'Piutang Usaha', 'type' => 'receivable', 'description' => 'Piutang Usaha'],
            ['code' => 'PIKR', 'name' => 'Piutang Karyawan', 'type' => 'receivable', 'description' => 'Piutang Karyawan'],
        ];

        foreach ($categories as $category) {
            DebtReceivableCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
