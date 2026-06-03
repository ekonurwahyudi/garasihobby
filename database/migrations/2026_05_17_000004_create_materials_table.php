<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_category_id')->constrained();
            $table->string('sku', 50)->nullable()->unique();
            $table->string('name', 200);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('cost_price', 14, 2)->nullable();
            $table->integer('min_stock')->default(0);
            $table->string('binrow', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
