<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('answer');
            $table->boolean('is_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_users');
    }
};
