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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('master_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_seed')->default(true);
        });

        Schema::create('status_courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('status_assessments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('status_assessments_seeds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('status_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
