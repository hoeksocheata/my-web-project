<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // អ្នកលក់ (Admin)
            $table->string('customer_name')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');                 // រក្សាទុកឈ្មោះ ដើម្បីវិក្កយបត្រនៅដដែល ទោះសៀវភៅត្រូវលុប
            $table->decimal('price', 8, 2);          // តម្លៃនៅពេលលក់
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
