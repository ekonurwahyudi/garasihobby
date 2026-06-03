<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles & permissions sesuai modul Garasi Hobby.
     * Pattern: module.action (create, view, edit, delete, approve)
     */
    public function run(): void
    {
        // Reset cache permission Spatie sebelum seed
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data - User
            'users.create',
            'users.view',
            'users.edit',
            'users.delete',

            // Master Data - Role
            'roles.create',
            'roles.view',
            'roles.edit',
            'roles.delete',

            // Master Data - Item Checklist
            'checklist.create',
            'checklist.view',
            'checklist.edit',
            'checklist.delete',

            // Master Data - Material
            'materials.create',
            'materials.view',
            'materials.edit',
            'materials.delete',

            // Master Data - Paket Promo
            'promo.create',
            'promo.view',
            'promo.edit',
            'promo.delete',

            // Master Data - Keuangan
            'finance-master.create',
            'finance-master.view',
            'finance-master.edit',
            'finance-master.delete',
            'bank-accounts.create',
            'bank-accounts.view',
            'bank-accounts.edit',
            'bank-accounts.delete',

            // Operasional - Pelanggan
            'customers.create',
            'customers.view',
            'customers.edit',
            'customers.delete',

            // Operasional - Order
            'orders.create',
            'orders.view',
            'orders.edit',
            'orders.delete',
            'orders.qc',
            'orders.payment',

            // Operasional - Pembelian Material
            'purchases.create',
            'purchases.view',
            'purchases.edit',
            'purchases.delete',
            'purchases.approve',

            // Keuangan
            'finance-transactions.create',
            'finance-transactions.view',
            'finance-transactions.edit',
            'finance-transactions.delete',

            // Notifikasi
            'notifications.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // === ROLES ===
        $superadmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $cs         = Role::firstOrCreate(['name' => 'Customer Service', 'guard_name' => 'web']);
        $mekanik    = Role::firstOrCreate(['name' => 'Mekanik', 'guard_name' => 'web']);
        $qc         = Role::firstOrCreate(['name' => 'QC', 'guard_name' => 'web']);

        // Superadmin: full access
        $superadmin->syncPermissions(Permission::all());

        // Customer Service
        $cs->syncPermissions([
            'dashboard.view',
            'customers.create', 'customers.view', 'customers.edit',
            'orders.create', 'orders.view', 'orders.edit', 'orders.payment',
            'purchases.create', 'purchases.view', 'purchases.edit',
            'notifications.view',
        ]);

        // Mekanik
        $mekanik->syncPermissions([
            'dashboard.view',
            'orders.view',
            'notifications.view',
        ]);

        // QC
        $qc->syncPermissions([
            'dashboard.view',
            'orders.view', 'orders.qc',
            'notifications.view',
        ]);
    }
}
