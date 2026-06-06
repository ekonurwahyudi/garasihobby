<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->string('cutoff_number', 30)->unique();
            $table->enum('cutoff_type', ['monthly', 'quarterly', 'yearly']);
            $table->unsignedSmallInteger('cutoff_year');
            $table->unsignedTinyInteger('cutoff_month')->nullable();
            $table->unsignedTinyInteger('cutoff_quarter')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_revenue', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('net_revenue', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['cutoff_type', 'cutoff_year', 'cutoff_month', 'cutoff_quarter'], 'revenue_cutoffs_period_unique');
        });

        Schema::table('revenue_sharings', function (Blueprint $table) {
            $table->foreignId('revenue_cutoff_id')->nullable()->after('id')->constrained('revenue_cutoffs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('revenue_sharings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revenue_cutoff_id');
        });

        Schema::dropIfExists('revenue_cutoffs');
    }
};
