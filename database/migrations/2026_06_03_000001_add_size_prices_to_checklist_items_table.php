<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->decimal('price_small', 14, 2)->default(0)->after('price');
            $table->decimal('price_medium', 14, 2)->default(0)->after('price_small');
            $table->decimal('price_large', 14, 2)->default(0)->after('price_medium');
        });

    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn(['price_small', 'price_medium', 'price_large']);
        });
    }
};
