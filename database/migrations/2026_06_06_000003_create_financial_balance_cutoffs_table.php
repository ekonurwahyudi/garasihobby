<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_balance_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->string('cutoff_number', 30)->unique();
            $table->unsignedSmallInteger('year');
            $table->date('cutoff_date');
            $table->string('label', 150)->nullable();
            $table->decimal('cash_bank', 15, 2)->default(0);
            $table->decimal('receivables', 15, 2)->default(0);
            $table->decimal('inventory', 15, 2)->default(0);
            $table->decimal('fixed_assets_gross', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('fixed_assets_net', 15, 2)->default(0);
            $table->decimal('total_assets', 15, 2)->default(0);
            $table->decimal('payables', 15, 2)->default(0);
            $table->decimal('owner_equity', 15, 2)->default(0);
            $table->decimal('current_year_profit', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);
            $table->decimal('total_equity', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['year', 'cutoff_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_balance_cutoffs');
    }
};
