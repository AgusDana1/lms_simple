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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->decimal('max_score', 5, 2)->default(100);
            $table->timestamp('available_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['course_id', 'due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
