<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('binrow');
        });

        Schema::create('material_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->integer('previous_qty');
            $table->integer('actual_qty');
            $table->integer('difference_qty');
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_stock_adjustments');

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
