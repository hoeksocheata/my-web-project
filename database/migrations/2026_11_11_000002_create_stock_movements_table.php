<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // អ្នកធ្វើសកម្មភាព
            $table->string('type', 20);          // initial | sale | void | restock | adjust
            $table->integer('change');           // +/- ចំនួន
            $table->unsignedInteger('balance');  // ស្តុកនៅសល់ក្រោយសកម្មភាព
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['book_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
