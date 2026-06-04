<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_purchases', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('evidence_paths')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('finance_transaction_id')->nullable()->after('bank_account_id')->constrained('finance_transactions')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('paid_at')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('finance_transaction_id')->nullable()->after('bank_account_id')->constrained('finance_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['finance_transaction_id']);
            $table->dropColumn(['bank_account_id', 'finance_transaction_id']);
        });

        Schema::table('material_purchases', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['finance_transaction_id']);
            $table->dropColumn(['bank_account_id', 'finance_transaction_id']);
        });
    }
};
