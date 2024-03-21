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
        Schema::create('assessment_courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->integer('assessment_canvas_id');
            $table->dateTime('due_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['assessment_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_courses');
    }
};
