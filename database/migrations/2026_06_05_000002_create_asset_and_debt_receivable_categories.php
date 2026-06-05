<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('debt_receivable_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->enum('type', ['debt', 'receivable', 'both'])->default('both');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->foreignId('asset_category_id')->nullable()->after('asset_category')->constrained('asset_categories')->nullOnDelete();
        });

        Schema::table('debt_receivables', function (Blueprint $table) {
            $table->foreignId('debt_receivable_category_id')->nullable()->after('category')->constrained('debt_receivable_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('debt_receivables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('debt_receivable_category_id');
        });

        Schema::table('asset_purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_category_id');
        });

        Schema::dropIfExists('debt_receivable_categories');
        Schema::dropIfExists('asset_categories');
    }
};
