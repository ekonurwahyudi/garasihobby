<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'orders',
        'customers',
        'checklist_items',
        'checklist_categories',
        'materials',
        'promo_packages',
        'users',
    ];

    public function up(): void
    {
        // Bersihkan data lama agar tidak muncul kembali setelah global scope
        // SoftDeletes dihapus. Urutan ini menjaga foreign key tetap valid.
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                DB::table($table)->whereNotNull('deleted_at')->delete();
            }
        }

        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->softDeletes();
                });
            }
        }
    }
};
