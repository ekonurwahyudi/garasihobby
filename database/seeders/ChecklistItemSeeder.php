<?php

namespace Database\Seeders;

use App\Models\ChecklistCategory;
use App\Models\ChecklistItem;
use Illuminate\Database\Seeder;

class ChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'category' => ['code' => 'SHK', 'name' => 'Shaking'],
                'items' => [
                    ['name' => 'Cek kaki-kaki', 'small' => 150000, 'medium' => 200000, 'large' => 250000],
                    ['name' => 'Rotasi ban', 'small' => 150000, 'medium' => 150000, 'large' => 150000],
                ],
            ],
            [
                'category' => ['code' => 'STR', 'name' => 'Steering'],
                'items' => [
                    ['name' => 'Ganti Tied Rod', 'small' => 150000, 'medium' => 125000, 'large' => 150000],
                    ['name' => 'Ganti rack end', 'small' => 150000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Service Rack steer', 'small' => 450000, 'medium' => 600000, 'large' => 850000],
                    ['name' => 'Bongkar Pasang Rack steer', 'small' => 250000, 'medium' => 350000, 'large' => 500000],
                    ['name' => 'Kalibrasi steer', 'small' => 75000, 'medium' => 100000, 'large' => 100000],
                    ['name' => 'Ganti bearing roda', 'small' => 250000, 'medium' => 250000, 'large' => 250000],
                    ['name' => 'Press bearing roda', 'small' => 75000, 'medium' => 125000, 'large' => 125000],
                ],
            ],
            [
                'category' => ['code' => 'SHB', 'name' => 'Shockbreaker'],
                'items' => [
                    ['name' => 'Ganti Shock Depan', 'small' => 150000, 'medium' => 150000, 'large' => 200000],
                    ['name' => 'Ganti Mounting shock', 'small' => 150000, 'medium' => 150000, 'large' => 200000],
                    ['name' => 'Ganti Support Shock', 'small' => 150000, 'medium' => 150000, 'large' => 200000],
                    ['name' => 'Ganti Per Shock', 'small' => 150000, 'medium' => 200000, 'large' => 250000],
                    ['name' => 'Service Shock', 'small' => 250000, 'medium' => 350000, 'large' => 500000],
                ],
            ],
            [
                'category' => ['code' => 'BLA', 'name' => 'Bushing & Lower Arm'],
                'items' => [
                    ['name' => 'Press Busing Arm', 'small' => 100000, 'medium' => 150000, 'large' => 200000],
                    ['name' => 'Ganti lower Arm Assy', 'small' => 150000, 'medium' => 200000, 'large' => 300000],
                    ['name' => 'Ganti Upper Arm', 'small' => 150000, 'medium' => 200000, 'large' => 300000],
                    ['name' => 'Ganti Trailing Arm Bushing', 'small' => 150000, 'medium' => 150000, 'large' => 200000],
                    ['name' => 'Ganti Engine Mounting', 'small' => 150000, 'medium' => 250000, 'large' => 350000],
                ],
            ],
            [
                'category' => ['code' => 'BST', 'name' => 'Ball Join & Stabilizer'],
                'items' => [
                    ['name' => 'Ganti Ball join atas', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Ganti Link Stabilizer', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Ganti Stabilizer bushing', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                ],
            ],
            [
                'category' => ['code' => 'BRS', 'name' => 'Brake Service'],
                'items' => [
                    ['name' => 'Service rem depan', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Service rem belakang', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Bubut Disc Brake', 'small' => 150000, 'medium' => 200000, 'large' => 250000],
                    ['name' => 'Ganti kampas rem', 'small' => 100000, 'medium' => 150000, 'large' => 150000],
                    ['name' => 'Service kaliper', 'small' => 150000, 'medium' => 200000, 'large' => 250000],
                ],
            ],
        ];

        foreach ($groups as $group) {
            $category = ChecklistCategory::withTrashed()->updateOrCreate(
                ['code' => $group['category']['code']],
                ['name' => $group['category']['name'], 'deleted_at' => null]
            );

            foreach ($group['items'] as $item) {
                ChecklistItem::withTrashed()->updateOrCreate(
                    [
                        'checklist_category_id' => $category->id,
                        'name' => $item['name'],
                    ],
                    [
                        'price' => $item['small'],
                        'price_small' => $item['small'],
                        'price_medium' => $item['medium'],
                        'price_large' => $item['large'],
                        'is_active' => true,
                        'deleted_at' => null,
                    ]
                );
            }
        }
    }
}
