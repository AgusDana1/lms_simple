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
        // 1. Fix quiz_attempts column: quizz_id -> quiz_id (if still quizz_id)
        if (Schema::hasTable('quiz_attempts')) {
            if (Schema::hasColumn('quiz_attempts', 'quizz_id') && ! Schema::hasColumn('quiz_attempts', 'quiz_id')) {
                Schema::table('quiz_attempts', function (Blueprint $table) {
                    $table->dropForeign(['quizz_id']);
                    $table->dropUnique(['quizz_id', 'student_id', 'attempt_number']);
                    $table->renameColumn('quizz_id', 'quiz_id');
                });

                Schema::table('quiz_attempts', function (Blueprint $table) {
                    $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
                    $table->unique(['quiz_id', 'student_id', 'attempt_number']);
                });
            }
        }

        // 2. Fix quiz_answers unique constraint:
        // Originally: $table->unique('quiz_attempt_id', 'question_id'); created unique key named 'question_id' on 'quiz_attempt_id' only
        if (Schema::hasTable('quiz_answers')) {
            $indexes = DB::select("SHOW INDEX FROM quiz_answers WHERE Key_name = 'question_id'");
            if (! empty($indexes)) {
                Schema::table('quiz_answers', function (Blueprint $table) {
                    $table->dropForeign('quiz_answers_quiz_attempt_id_foreign');
                    $table->dropUnique('question_id');
                });

                Schema::table('quiz_answers', function (Blueprint $table) {
                    $table->foreign('quiz_attempt_id')->references('id')->on('quiz_attempts')->cascadeOnDelete();
                });
            }

            // Create composite unique constraint on (quiz_attempt_id, question_id)
            $compositeIndex = DB::select("SHOW INDEX FROM quiz_answers WHERE Key_name = 'quiz_answers_attempt_question_unique'");
            if (empty($compositeIndex)) {
                Schema::table('quiz_answers', function (Blueprint $table) {
                    $table->unique(['quiz_attempt_id', 'question_id'], 'quiz_answers_attempt_question_unique');
                });
            }
        }

        // 3. Add user_id to discussion_replies
        if (Schema::hasTable('discussion_replies')) {
            if (! Schema::hasColumn('discussion_replies', 'user_id')) {
                Schema::table('discussion_replies', function (Blueprint $table) {
                    $table->foreignId('user_id')->nullable()->after('discussion_id')->constrained('users')->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('discussion_replies') && Schema::hasColumn('discussion_replies', 'user_id')) {
            Schema::table('discussion_replies', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasTable('quiz_answers')) {
            $compositeIndex = DB::select("SHOW INDEX FROM quiz_answers WHERE Key_name = 'quiz_answers_attempt_question_unique'");
            if (! empty($compositeIndex)) {
                Schema::table('quiz_answers', function (Blueprint $table) {
                    $table->dropUnique('quiz_answers_attempt_question_unique');
                });
            }
        }
    }
};
