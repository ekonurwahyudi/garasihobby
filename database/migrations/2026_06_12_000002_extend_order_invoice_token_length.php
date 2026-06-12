<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'invoice_token')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE orders ALTER COLUMN invoice_token TYPE VARCHAR(32)'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE orders MODIFY invoice_token VARCHAR(32) NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'invoice_token')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE orders ALTER COLUMN invoice_token TYPE VARCHAR(10)'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE orders MODIFY invoice_token VARCHAR(10) NULL'),
            default => null,
        };
    }
};
