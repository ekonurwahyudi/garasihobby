<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['income', 'expense'])->default('expense')->after('transaction_number');
            $table->string('activity', 255)->nullable()->after('bank_account_id');
            $table->json('evidence_paths')->nullable()->after('notes');
        });

        DB::table('finance_transactions')
            ->select('finance_transactions.id', 'finance_transactions.description', 'finance_categories.type')
            ->join('finance_items', 'finance_transactions.finance_item_id', '=', 'finance_items.id')
            ->join('finance_categories', 'finance_items.finance_category_id', '=', 'finance_categories.id')
            ->orderBy('finance_transactions.id')
            ->get()
            ->each(function ($transaction) {
                DB::table('finance_transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'transaction_type' => $transaction->type,
                        'activity' => $transaction->description,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'activity', 'evidence_paths']);
        });
    }
};
