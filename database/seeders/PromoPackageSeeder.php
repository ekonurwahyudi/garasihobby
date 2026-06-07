<?php

namespace Database\Seeders;

use App\Models\PromoPackage;
use Illuminate\Database\Seeder;

class PromoPackageSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = now()->toDateString();
        $validUntil = now()->addMonths(2)->toDateString();

        $packages = [
            ['name' => 'Basic', 'price_small' => 275000, 'price_medium' => 350000, 'price_large' => 400000],
            ['name' => 'Basic +', 'price_small' => 349000, 'price_medium' => 429000, 'price_large' => 499000],
            ['name' => 'Comfort', 'price_small' => 449000, 'price_medium' => 549000, 'price_large' => 699000],
            ['name' => 'Premium', 'price_small' => 499000, 'price_medium' => 599000, 'price_large' => 749000],
        ];

        foreach ($packages as $package) {
            PromoPackage::updateOrCreate(
                ['name' => $package['name']],
                [
                    ...$package,
                    'price' => $package['price_small'],
                    'description' => 'Paket promo ' . $package['name'],
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil,
                    'is_active' => true,
                ]
            );
        }
    }
}
