<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // អតិថិជនដែលកម្មង់
            $table->string('status', 20)->default('pending')->index();         // pending | approved | rejected | cancelled
            $table->text('note')->nullable();                                  // កំណត់ចំណាំពីអតិថិជន
            $table->text('reject_reason')->nullable();                         // មូលហេតុបដិសេធ
            $table->decimal('total', 10, 2)->default(0);
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete(); // វិក្កយបត្រ ពេលអនុម័ត
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
