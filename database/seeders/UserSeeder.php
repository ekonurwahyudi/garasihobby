<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed user awal: 1 Superadmin + 1 demo CS + 1 Mekanik + 1 QC.
     */
    public function run(): void
    {
        $defaultPassword = env('SEED_DEFAULT_PASSWORD', 'password');

        $superadmin = User::updateOrCreate(
            ['email' => 'admin@garasihobby.com'],
            [
                'name'              => 'Super Admin',
                'jabatan'           => 'Owner',
                'phone'             => '081200000001',
                'password'          => Hash::make($defaultPassword),
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $superadmin->syncRoles(['Superadmin']);

        $cs = User::updateOrCreate(
            ['email' => 'cs@garasihobby.com'],
            [
                'name'              => 'Customer Service',
                'jabatan'           => 'CS Bengkel',
                'phone'             => '081200000002',
                'password'          => Hash::make($defaultPassword),
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $cs->syncRoles(['Customer Service']);

        $mekanik = User::updateOrCreate(
            ['email' => 'mekanik@garasihobby.com'],
            [
                'name'              => 'Mekanik Senior',
                'jabatan'           => 'Mekanik',
                'phone'             => '081200000003',
                'password'          => Hash::make($defaultPassword),
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $mekanik->syncRoles(['Mekanik']);

        $qc = User::updateOrCreate(
            ['email' => 'qc@garasihobby.com'],
            [
                'name'              => 'Quality Control',
                'jabatan'           => 'QC',
                'phone'             => '081200000004',
                'password'          => Hash::make($defaultPassword),
                'status'            => 'aktif',
                'email_verified_at' => now(),
            ]
        );
        $qc->syncRoles(['QC']);
    }
}
