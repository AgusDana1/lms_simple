<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = Auth::user();
        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya dosen pengampu yang dapat membuat kuis.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'available_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'max_attempts' => $validated['max_attempts'],
            'passing_score' => $validated['passing_score'],
            'available_at' => $validated['available_at'] ?? now(),
            'due_at' => $validated['due_at'] ?? null,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('quizzes.questions', $quiz)
            ->with('success', 'Kuis berhasil dibuat! Silakan tambahkan butir soal.');
    }

    public function show(Quiz $quiz): View
    {
        $user = Auth::user();
        $course = $quiz->course;

        $isMember = $course->members()->where('user_id', $user->id)->exists();
        $isLecturer = $course->created_by === $user->id || $user->isAdmin();

        if (! $isMember && ! $isLecturer) {
            abort(403, 'Akses ditolak.');
        }

        $quiz->load(['questions.options', 'attempts' => fn ($q) => $q->where('student_id', $user->id)->latest()]);

        $myAttempts = $quiz->attempts;
        $bestScore = $myAttempts->where('status', 'graded')->max('score');
        $canAttempt = $myAttempts->count() < $quiz->max_attempts;

        // If lecturer, load all student attempts
        $allAttempts = collect();
        if ($isLecturer) {
            $allAttempts = $quiz->attempts()->with('student')->latest('started_at')->get();
        }

        return view('quizzes.show', compact(
            'quiz',
            'course',
            'isLecturer',
            'myAttempts',
            'bestScore',
            'canAttempt',
            'allAttempts'
        ));
    }

    public function questions(Quiz $quiz): View
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses mengelola soal kuis ini.');
        }

        $quiz->load(['questions.options']);

        return view('quizzes.questions', compact('quiz', 'course'));
    }

    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:multiple_choice,true_false,essay'],
            'text' => ['required', 'string'],
            'points' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'correct_option' => ['nullable'],
            'tf_correct' => ['nullable', 'in:true,false'],
            'auto_distribute' => ['nullable', 'boolean'],
        ]);

        $sortOrder = $quiz->questions()->max('sort_order') + 1;

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => $validated['type'],
            'text' => $validated['text'],
            'points' => $validated['points'],
            'sort_order' => $sortOrder,
        ]);

        if ($validated['type'] === 'multiple_choice' && ! empty($validated['options'])) {
            $correctIdx = (int) ($validated['correct_option'] ?? 0);
            foreach ($validated['options'] as $idx => $optText) {
                if (filled($optText)) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optText,
                        'is_correct' => ($idx == $correctIdx),
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        } elseif ($validated['type'] === 'true_false') {
            $isTrueCorrect = ($validated['tf_correct'] ?? 'true') === 'true';
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => 'Benar (True)',
                'is_correct' => $isTrueCorrect,
                'sort_order' => 1,
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => 'Salah (False)',
                'is_correct' => ! $isTrueCorrect,
                'sort_order' => 2,
            ]);
        }

        if ($request->boolean('auto_distribute')) {
            $this->distributePointsInternal($quiz);
            $totalCount = $quiz->questions()->count();
            $ppq = round(100 / $totalCount, 2);

            return back()->with('success', "Butir soal berhasil ditambahkan! Seluruh {$totalCount} butir soal otomatis dibagi rata (~{$ppq} poin/soal) sehingga total bobot tetap 100 poin.");
        }

        return back()->with('success', 'Butir soal berhasil ditambahkan!');
    }

    public function updateQuestion(Request $request, Question $question): RedirectResponse
    {
        $user = Auth::user();
        $quiz = $question->quiz;
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:multiple_choice,true_false,essay'],
            'text' => ['required', 'string'],
            'points' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'correct_option' => ['nullable'],
            'tf_correct' => ['nullable', 'in:true,false'],
        ]);

        $question->update([
            'type' => $validated['type'],
            'text' => $validated['text'],
            'points' => $validated['points'],
        ]);

        $question->options()->delete();

        if ($validated['type'] === 'multiple_choice' && ! empty($validated['options'])) {
            $correctIdx = (int) ($validated['correct_option'] ?? 0);
            foreach ($validated['options'] as $idx => $optText) {
                if (filled($optText)) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optText,
                        'is_correct' => ($idx == $correctIdx),
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        } elseif ($validated['type'] === 'true_false') {
            $isTrueCorrect = ($validated['tf_correct'] ?? 'true') === 'true';
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => 'Benar (True)',
                'is_correct' => $isTrueCorrect,
                'sort_order' => 1,
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => 'Salah (False)',
                'is_correct' => ! $isTrueCorrect,
                'sort_order' => 2,
            ]);
        }

        return back()->with('success', 'Butir soal berhasil diperbarui!');
    }

    public function distributePoints(Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $count = $quiz->questions()->count();
        if ($count === 0) {
            return back()->with('error', 'Belum ada butir soal pada kuis ini.');
        }

        $this->distributePointsInternal($quiz);

        return back()->with('success', "Bobot seluruh {$count} butir soal berhasil dibagi rata menjadi total 100 poin!");
    }

    public function generateTemplateQuestions(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'count' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $addCount = (int) ($validated['count'] ?? 10);
        $currentCount = $quiz->questions()->count();
        $startOrder = (int) ($quiz->questions()->max('sort_order') ?? 0);

        for ($i = 1; $i <= $addCount; $i++) {
            $num = $currentCount + $i;
            $q = Question::create([
                'quiz_id' => $quiz->id,
                'type' => 'multiple_choice',
                'text' => "Pertanyaan Nomor {$num} (Silakan ubah teks soal ini)",
                'points' => 1,
                'sort_order' => $startOrder + $i,
            ]);

            QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => 'Pilihan Jawaban A (Kunci)',
                'is_correct' => true,
                'sort_order' => 1,
            ]);
            QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => 'Pilihan Jawaban B',
                'is_correct' => false,
                'sort_order' => 2,
            ]);
            QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => 'Pilihan Jawaban C',
                'is_correct' => false,
                'sort_order' => 3,
            ]);
            QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => 'Pilihan Jawaban D',
                'is_correct' => false,
                'sort_order' => 4,
            ]);
        }

        $this->distributePointsInternal($quiz);
        $newTotalCount = $currentCount + $addCount;
        $pointsPerQuestion = round(100 / $newTotalCount, 2);

        return back()->with('success', "Berhasil menambahkan {$addCount} butir soal baru. Sekarang total ada {$newTotalCount} butir soal dengan bobot otomatis dibagi rata (~{$pointsPerQuestion} poin/soal) sehingga total nilai kuis tetap 100 poin!");
    }

    private function distributePointsInternal(Quiz $quiz): void
    {
        $questions = $quiz->questions()->orderBy('sort_order', 'asc')->get();
        $count = $questions->count();
        if ($count === 0) {
            return;
        }

        $basePoints = round(100 / $count, 2);
        $assigned = 0;

        foreach ($questions as $index => $q) {
            if ($index === $count - 1) {
                $qPoints = round(100 - $assigned, 2);
            } else {
                $qPoints = $basePoints;
                $assigned += $qPoints;
            }
            $q->update(['points' => $qPoints]);
        }
    }

    public function destroyQuestion(Question $question): RedirectResponse
    {
        $user = Auth::user();
        $quiz = $question->quiz;
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $question->delete();

        if ($quiz->questions()->count() > 0) {
            $this->distributePointsInternal($quiz);
        }

        return back()->with('success', 'Soal berhasil dihapus dan bobot butir soal yang tersisa otomatis disesuaikan agar total poin tetap 100.');
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'available_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'max_attempts' => $validated['max_attempts'],
            'passing_score' => $validated['passing_score'],
            'available_at' => $validated['available_at'] ?? $quiz->available_at,
            'due_at' => $validated['due_at'],
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Pengaturan kuis berhasil diperbarui!');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $title = $quiz->title;
        $quiz->delete();

        return redirect()->route('courses.show', ['course' => $course, 'tab' => 'quizzes'])
            ->with('success', 'Kuis "'.$title.'" berhasil dihapus.');
    }

    public function start(Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();

        // Check availability
        if ($quiz->available_at && now()->lt($quiz->available_at)) {
            return back()->with('error', 'Kuis ini belum dibuka.');
        }
        if ($quiz->due_at && now()->gt($quiz->due_at)) {
            return back()->with('error', 'Batas waktu pengerjaan kuis telah berakhir.');
        }

        // Check attempts
        $attemptCount = $quiz->attempts()->where('student_id', $user->id)->count();
        if ($attemptCount >= $quiz->max_attempts) {
            return back()->with('error', 'Anda telah mencapai batas maksimum pengerjaan kuis ini.');
        }

        // Check if there is an in-progress attempt
        $existing = $quiz->attempts()
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return redirect()->route('quizzes.take', $existing);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'attempt_number' => $attemptCount + 1,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('quizzes.take', $attempt);
    }

    public function take(QuizAttempt $attempt): View|RedirectResponse
    {
        $user = Auth::user();

        if ($attempt->student_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('quizzes.result', $attempt)
                ->with('info', 'Percobaan kuis ini sudah diselesaikan.');
        }

        $quiz = $attempt->quiz;

        // Check if time expired
        $endTime = $attempt->started_at->copy()->addMinutes($quiz->duration_minutes);
        if (now()->gt($endTime)) {
            // Auto submit
            return $this->autoSubmitExpiredAttempt($attempt);
        }

        $remainingSeconds = max(0, $endTime->diffInSeconds(now()));

        $questionsQuery = $quiz->questions()->with('options');
        if ($quiz->shuffle_questions) {
            $questions = $questionsQuery->inRandomOrder()->get();
        } else {
            $questions = $questionsQuery->orderBy('sort_order', 'asc')->get();
        }

        $savedAnswers = $attempt->answers->keyBy('question_id');

        return view('quizzes.take', compact('attempt', 'quiz', 'questions', 'remainingSeconds', 'savedAnswers'));
    }

    public function submitAttempt(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $user = Auth::user();

        if ($attempt->student_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('quizzes.result', $attempt);
        }

        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->get();

        $totalMaxPoints = $questions->sum('points');
        $earnedPoints = 0;
        $hasEssay = false;

        $answersData = $request->input('answers', []);

        foreach ($questions as $question) {
            $userAnswer = $answersData[$question->id] ?? null;

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $selectedOptionId = $userAnswer ? (int) $userAnswer : null;
                $isCorrect = false;

                if ($selectedOptionId) {
                    $option = $question->options->firstWhere('id', $selectedOptionId);
                    $isCorrect = $option ? (bool) $option->is_correct : false;
                }

                $qScore = $isCorrect ? $question->points : 0;
                $earnedPoints += $qScore;

                QuizAnswer::updateOrCreate(
                    ['quiz_attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'question_option_id' => $selectedOptionId,
                        'is_correct' => $isCorrect,
                        'score' => $qScore,
                    ]
                );
            } else {
                // Essay
                $hasEssay = true;
                QuizAnswer::updateOrCreate(
                    ['quiz_attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'answer_text' => $userAnswer,
                        'is_correct' => null,
                        'score' => null,
                    ]
                );
            }
        }

        // Calculate final score scaled to 100
        $finalScore = $totalMaxPoints > 0 ? round(($earnedPoints / $totalMaxPoints) * 100, 2) : 0;
        $status = $hasEssay ? 'submitted' : 'graded';

        $attempt->update([
            'submitted_at' => now(),
            'score' => $finalScore,
            'status' => $status,
        ]);

        if ($status === 'graded') {
            // Record in Grade table if this attempt is higher than previous
            $existingGrade = Grade::where('course_id', $quiz->course_id)
                ->where('student_id', $user->id)
                ->where('component', 'Kuis: '.$quiz->title)
                ->first();

            if (! $existingGrade || $finalScore > $existingGrade->score) {
                Grade::updateOrCreate(
                    [
                        'course_id' => $quiz->course_id,
                        'student_id' => $user->id,
                        'component' => 'Kuis: '.$quiz->title,
                    ],
                    [
                        'score' => $finalScore,
                        'notes' => 'Attempt #'.$attempt->attempt_number,
                    ]
                );
            }
        }

        return redirect()->route('quizzes.result', $attempt)
            ->with('success', 'Kuis berhasil diselesaikan!');
    }

    public function result(QuizAttempt $attempt): View
    {
        $user = Auth::user();
        $quiz = $attempt->quiz;
        $course = $quiz->course;

        $isLecturer = $course->created_by === $user->id || $user->isAdmin();
        if ($attempt->student_id !== $user->id && ! $isLecturer) {
            abort(403, 'Akses ditolak.');
        }

        $attempt->load(['quiz.questions.options', 'answers.option', 'student.studentProfile']);

        $isPassed = $quiz->passing_score ? ($attempt->score >= $quiz->passing_score) : true;

        return view('quizzes.result', compact('attempt', 'quiz', 'course', 'isPassed', 'isLecturer'));
    }

    private function autoSubmitExpiredAttempt(QuizAttempt $attempt): RedirectResponse
    {
        $attempt->update([
            'submitted_at' => now(),
            'score' => 0,
            'status' => 'graded',
        ]);

        return redirect()->route('quizzes.result', $attempt)
            ->with('warning', 'Waktu pengerjaan kuis telah habis. Kuis otomatis dikumpulkan.');
    }

    public function generateWithAi(Request $request, Quiz $quiz, GeminiService $gemini): RedirectResponse
    {
        $user = Auth::user();
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'count' => ['required', 'integer', 'min:1', 'max:50'],
            'difficulty' => ['required', 'in:dasar,sedang,lanjutan'],
            'custom_instruction' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $generated = $gemini->generateQuizQuestions(
                $validated['topic'],
                (int) $validated['count'],
                $validated['difficulty'],
                $validated['custom_instruction'] ?? null
            );

            if (empty($generated)) {
                return back()->with('error', 'AI tidak berhasil menghasilkan butir soal. Coba ulangi dengan topik yang lebih spesifik.');
            }

            $startOrder = (int) ($quiz->questions()->max('sort_order') ?? 0);
            $addedCount = 0;

            foreach ($generated as $qData) {
                if (empty($qData['text'])) {
                    continue;
                }

                $type = in_array($qData['type'] ?? '', ['multiple_choice', 'true_false', 'essay'])
                    ? $qData['type']
                    : 'multiple_choice';

                $q = Question::create([
                    'quiz_id' => $quiz->id,
                    'type' => $type,
                    'text' => $qData['text'],
                    'points' => 1,
                    'sort_order' => $startOrder + $addedCount + 1,
                ]);

                if ($type === 'multiple_choice' && ! empty($qData['options']) && is_array($qData['options'])) {
                    foreach ($qData['options'] as $optIdx => $opt) {
                        QuestionOption::create([
                            'question_id' => $q->id,
                            'option_text' => $opt['option_text'] ?? ('Pilihan '.chr(65 + $optIdx)),
                            'is_correct' => ! empty($opt['is_correct']),
                            'sort_order' => $optIdx + 1,
                        ]);
                    }
                } elseif ($type === 'true_false') {
                    $isTrueCorrect = ($qData['tf_correct'] ?? 'true') === 'true';
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => 'Benar (True)',
                        'is_correct' => $isTrueCorrect,
                        'sort_order' => 1,
                    ]);
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => 'Salah (False)',
                        'is_correct' => ! $isTrueCorrect,
                        'sort_order' => 2,
                    ]);
                }

                $addedCount++;
            }

            $this->distributePointsInternal($quiz);
            $totalCount = $quiz->questions()->count();
            $ppq = round(100 / $totalCount, 2);

            return back()->with('success', "Berhasil membuat {$addedCount} butir soal baru dengan AI! Total sekarang ada {$totalCount} butir soal dan bobot otomatis disesuaikan (~{$ppq} poin/soal, total 100 poin).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal generate soal kuis dengan AI: '.$e->getMessage());
        }
    }

    public function evaluateEssayWithAi(QuizAnswer $answer, GeminiService $gemini): JsonResponse
    {
        $user = Auth::user();
        $attempt = $answer->attempt;
        $quiz = $attempt->quiz;
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $question = $answer->question;
        if (! $question || $question->type !== 'essay') {
            return response()->json(['success' => false, 'message' => 'Soal ini bukan bertipe esai.'], 400);
        }

        if (empty($answer->answer_text)) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak mengisi jawaban esai ini.'], 400);
        }

        try {
            $evaluation = $gemini->summarizeAndEvaluateEssay($question, $answer);

            return response()->json([
                'success' => true,
                'data' => $evaluation,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal evaluasi esai dengan AI: '.$e->getMessage(),
            ], 500);
        }
    }

    public function gradeEssayAnswer(Request $request, QuizAnswer $answer): RedirectResponse
    {
        $user = Auth::user();
        $attempt = $answer->attempt;
        $quiz = $attempt->quiz;
        $course = $quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $question = $answer->question;
        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$question->points],
        ]);

        $score = (float) $validated['score'];
        $answer->update([
            'score' => $score,
            'is_correct' => ($score > 0),
        ]);

        // Recalculate attempt score
        $questions = $quiz->questions()->get();
        $totalMaxPoints = $questions->sum('points');
        $answers = $attempt->answers()->get();
        $earnedPoints = $answers->sum('score');

        $finalScore = $totalMaxPoints > 0 ? round(($earnedPoints / $totalMaxPoints) * 100, 2) : 0;

        // Check if there are still ungraded essay answers
        $ungradedEssays = $answers->filter(function ($ans) use ($quiz) {
            $q = $quiz->questions->firstWhere('id', $ans->question_id);

            return $q && $q->type === 'essay' && $ans->score === null;
        });

        $status = $ungradedEssays->isEmpty() ? 'graded' : 'submitted';

        $attempt->update([
            'score' => $finalScore,
            'status' => $status,
        ]);

        if ($status === 'graded') {
            Grade::updateOrCreate(
                [
                    'course_id' => $quiz->course_id,
                    'student_id' => $attempt->student_id,
                    'component' => 'Kuis: '.$quiz->title,
                ],
                [
                    'score' => $finalScore,
                    'notes' => 'Attempt #'.$attempt->attempt_number,
                ]
            );
        }

        return back()->with('success', "Nilai butir esai berhasil disimpan! Skor akhir kuis: {$finalScore} / 100.");
    }
}
