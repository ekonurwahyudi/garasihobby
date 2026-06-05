<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('asset_number', 30)->unique();
            $table->string('asset_name', 150);
            $table->string('asset_category', 100)->nullable();
            $table->date('purchase_date');
            $table->string('supplier', 150)->nullable();
            $table->decimal('purchase_amount', 15, 2);
            $table->unsignedInteger('useful_life_years')->default(0);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'percentage', 'none'])->default('straight_line');
            $table->decimal('depreciation_percentage', 5, 2)->nullable();
            $table->decimal('book_value', 15, 2)->default(0);
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->json('asset_photo_paths')->nullable();
            $table->json('evidence_paths')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['menunggu_approval', 'disetujui', 'ditolak'])->default('menunggu_approval');
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

        Schema::create('debt_receivables', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 30)->unique();
            $table->enum('type', ['debt', 'receivable']);
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->string('party_name', 150);
            $table->string('category', 100)->nullable();
            $table->string('activity', 255);
            $table->decimal('amount', 15, 2);
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['belum_lunas', 'sebagian', 'lunas'])->default('belum_lunas');
            $table->enum('status', ['menunggu_approval', 'disetujui', 'ditolak', 'cancel'])->default('menunggu_approval');
            $table->text('notes')->nullable();
            $table->json('evidence_paths')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('debt_receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_receivable_id')->constrained('debt_receivables')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->json('evidence_paths')->nullable();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_receivable_payments');
        Schema::dropIfExists('debt_receivables');
        Schema::dropIfExists('asset_purchases');
    }
};
