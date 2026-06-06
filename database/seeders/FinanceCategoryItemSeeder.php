<?php

namespace Database\Seeders;

use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use Illuminate\Database\Seeder;

class FinanceCategoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Investasi' => FinanceCategory::updateOrCreate(
                ['code' => 'INV'],
                ['name' => 'Investasi', 'type' => 'income', 'description' => 'Pemasukan kategori investasi']
            ),
            'Operasional' => FinanceCategory::updateOrCreate(
                ['code' => 'OPS'],
                ['name' => 'Operasional', 'type' => 'expense', 'description' => 'Pengeluaran kategori operasional']
            ),
        ];

        $items = [
            ['category' => 'Investasi', 'code' => 'KLST', 'name' => 'KELISTRIKAN'],
            ['category' => 'Investasi', 'code' => 'KONI', 'name' => 'KONSTRUKSI INVESTASI'],
            ['category' => 'Investasi', 'code' => 'ADMI', 'name' => 'ADMINITRASI'],
            ['category' => 'Operasional', 'code' => 'KONO', 'name' => 'KONSTRUKSI OPERASIONAL'],
            ['category' => 'Operasional', 'code' => 'ISTF', 'name' => 'Intensif'],
            ['category' => 'Operasional', 'code' => 'BDPR', 'name' => 'Belanja Dapur'],
            ['category' => 'Operasional', 'code' => 'GAJI', 'name' => 'Gaji'],
            ['category' => 'Operasional', 'code' => 'AKER', 'name' => 'Tools Kerja'],
            ['category' => 'Operasional', 'code' => 'INET', 'name' => 'Internet'],
        ];

        foreach ($items as $item) {
            FinanceItem::updateOrCreate(
                ['code' => $item['code']],
                [
                    'finance_category_id' => $categories[$item['category']]->id,
                    'name' => $item['name'],
                    'description' => $item['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
