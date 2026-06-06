<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_sharings', function (Blueprint $table) {
            $table->id();
            $table->string('sharing_number', 30)->unique();
            $table->string('recipient_name', 150);
            $table->enum('cutoff_type', ['monthly', 'quarterly', 'yearly']);
            $table->unsignedSmallInteger('cutoff_year');
            $table->unsignedTinyInteger('cutoff_month')->nullable();
            $table->unsignedTinyInteger('cutoff_quarter')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_revenue', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('net_revenue', 15, 2)->default(0);
            $table->decimal('sharing_percentage', 5, 2);
            $table->decimal('sharing_amount', 15, 2);
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->json('evidence_paths')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('menunggu_approval');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_sharings');
    }
};
