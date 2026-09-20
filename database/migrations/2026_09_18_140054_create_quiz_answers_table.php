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
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();
            $table->unique('quiz_attempt_id', 'question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
