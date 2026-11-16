<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->after('status'); // unpaid | submitted | paid
            $table->string('payment_ref', 100)->nullable()->after('payment_status');   // លេខប្រតិបត្តិការ
            $table->string('payment_proof')->nullable()->after('payment_ref');         // រូបភស្តុតាង
            $table->timestamp('paid_at')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_ref', 'payment_proof', 'paid_at']);
        });
    }
};
