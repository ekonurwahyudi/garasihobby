<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('qty')->default(1)->after('price');
            $table->decimal('subtotal', 14, 2)->default(0)->after('qty');
        });

        DB::table('order_items')->update([
            'qty' => 1,
            'subtotal' => DB::raw('price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['qty', 'subtotal']);
        });
    }
};
