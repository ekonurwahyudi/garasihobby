<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('other_service_price', 14, 2)->default(0)->after('discount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 14, 2)->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('other_service_price');
        });
    }
};
