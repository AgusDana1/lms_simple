<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
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
            'points' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string'],
            'correct_option' => ['nullable'],
            'tf_correct' => ['nullable', 'in:true,false'],
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

        return back()->with('success', 'Butir soal berhasil ditambahkan!');
    }

    public function destroyQuestion(Question $question): RedirectResponse
    {
        $user = Auth::user();
        $course = $question->quiz->course;

        if ($course->created_by !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $question->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
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
}
