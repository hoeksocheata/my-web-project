<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');   // 1-5
            $table->string('comment', 500)->nullable();
            $table->timestamps();
            $table->unique(['book_id', 'user_id']);   // ១នាក់ ១វាយតម្លៃ ក្នុង១សៀវភៅ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
