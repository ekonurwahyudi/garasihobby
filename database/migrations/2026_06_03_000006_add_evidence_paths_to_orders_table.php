<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('evidence_work_paths')->nullable()->after('paid_at');
            $table->json('evidence_payment_paths')->nullable()->after('evidence_work_paths');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['evidence_work_paths', 'evidence_payment_paths']);
        });
    }
};
