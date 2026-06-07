<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promo_package_id')->nullable()->after('other_service_price')->constrained()->nullOnDelete();
            $table->string('promo_package_name', 150)->nullable()->after('promo_package_id');
            $table->decimal('promo_package_price', 14, 2)->default(0)->after('promo_package_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_package_id');
            $table->dropColumn(['promo_package_name', 'promo_package_price']);
        });
    }
};
