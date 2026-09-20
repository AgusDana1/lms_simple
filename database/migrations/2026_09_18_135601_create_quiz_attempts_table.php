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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quizz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('status', [
                'in_progress',
                'submitted',
                'graded',
            ])->default('in_progress');
            $table->timestamps();
            $table->unique(['quizz_id', 'student_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
