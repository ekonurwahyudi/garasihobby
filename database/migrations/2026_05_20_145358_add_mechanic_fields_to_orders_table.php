<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('mileage', 50)->nullable()->after('complaint');
            $table->string('km_service', 50)->nullable()->after('mileage');
            $table->string('km_return', 50)->nullable()->after('km_service');
            $table->string('head_mechanic', 100)->nullable()->after('km_return');
            $table->string('mechanic', 100)->nullable()->after('head_mechanic');
            $table->string('mechanic_number', 50)->nullable()->after('mechanic');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['mileage', 'km_service', 'km_return', 'head_mechanic', 'mechanic', 'mechanic_number']);
        });
    }
};
