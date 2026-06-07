<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_packages', function (Blueprint $table) {
            $table->decimal('price_small', 14, 2)->default(0)->after('price');
            $table->decimal('price_medium', 14, 2)->default(0)->after('price_small');
            $table->decimal('price_large', 14, 2)->default(0)->after('price_medium');
        });

        DB::table('promo_packages')->update([
            'price_small' => DB::raw('price'),
            'price_medium' => DB::raw('price'),
            'price_large' => DB::raw('price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('promo_packages', function (Blueprint $table) {
            $table->dropColumn(['price_small', 'price_medium', 'price_large']);
        });
    }
};
