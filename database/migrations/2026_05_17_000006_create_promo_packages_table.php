<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promo_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained();
            $table->timestamps();
            $table->unique(['promo_package_id', 'checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_package_items');
        Schema::dropIfExists('promo_packages');
    }
};
