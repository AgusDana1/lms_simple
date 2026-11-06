<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure quiz_attempts uses quiz_id
        if (Schema::hasTable('quiz_attempts')) {
            if (Schema::hasColumn('quiz_attempts', 'quizz_id') && ! Schema::hasColumn('quiz_attempts', 'quiz_id')) {
                try {
                    Schema::table('quiz_attempts', function (Blueprint $table) {
                        $table->dropForeign(['quizz_id']);
                        $table->dropUnique(['quizz_id', 'student_id', 'attempt_number']);
                        $table->renameColumn('quizz_id', 'quiz_id');
                    });

                    Schema::table('quiz_attempts', function (Blueprint $table) {
                        $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
                        $table->unique(['quiz_id', 'student_id', 'attempt_number']);
                    });
                } catch (Throwable $e) {
                    // Ignore if already fixed or not applicable
                }
            }
        }

        // 2. Ensure quiz_answers has composite unique ['quiz_attempt_id', 'question_id']
        // and does NOT have a unique constraint on quiz_attempt_id alone.
        if (Schema::hasTable('quiz_answers')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                try {
                    $indexes = DB::select("SHOW INDEX FROM quiz_answers WHERE Key_name = 'question_id'");
                    if (! empty($indexes)) {
                        Schema::table('quiz_answers', function (Blueprint $table) {
                            $table->dropForeign(['quiz_attempt_id']);
                            $table->dropUnique('question_id');
                        });

                        Schema::table('quiz_answers', function (Blueprint $table) {
                            $table->foreign('quiz_attempt_id')->references('id')->on('quiz_attempts')->cascadeOnDelete();
                        });
                    }
                } catch (Throwable $e) {
                    // Ignore index dropping error if doesn't exist
                }

                try {
                    $compositeIndex = DB::select("SHOW INDEX FROM quiz_answers WHERE Key_name = 'quiz_answers_attempt_question_unique'");
                    if (empty($compositeIndex)) {
                        Schema::table('quiz_answers', function (Blueprint $table) {
                            $table->unique(['quiz_attempt_id', 'question_id'], 'quiz_answers_attempt_question_unique');
                        });
                    }
                } catch (Throwable $e) {
                    // Ignore if composite index already exists
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
