<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class LmsFeatureTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Portal LMS');
    }

    public function test_user_can_login_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'dosen@lms.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_student_can_view_courses_and_enroll(): void
    {
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $this->assertNotNull($student);
        $this->assertNotNull($course);

        $response = $this->actingAs($student)->get('/courses');
        $response->assertStatus(200);

        // Enroll
        $enrollResponse = $this->actingAs($student)->post("/courses/{$course->id}/enroll");
        $enrollResponse->assertRedirect("/courses/{$course->id}");

        $this->assertTrue($course->members()->where('user_id', $student->id)->exists());
    }

    public function test_lecturer_can_create_course_material_and_assignment(): void
    {
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $this->assertNotNull($lecturer);

        // Create course
        $code = 'TEST-'.strtoupper(Str::random(8));
        $courseResponse = $this->actingAs($lecturer)->post('/courses', [
            'code' => $code,
            'name' => 'Mata Kuliah Pengujian Otomatis',
            'description' => 'Deskripsi untuk pengujian.',
            'is_published' => 1,
        ]);

        $course = Course::where('code', $code)->first();
        $this->assertNotNull($course);
        $courseResponse->assertRedirect("/courses/{$course->id}");

        // Create material
        $materialResponse = $this->actingAs($lecturer)->post("/courses/{$course->id}/materials", [
            'title' => 'Pengenalan Testing',
            'type' => 'text',
            'content' => 'Materi testing di Laravel.',
            'is_published' => 1,
        ]);
        $materialResponse->assertRedirect("/courses/{$course->id}?tab=materials");
        $this->assertDatabaseHas('materials', ['course_id' => $course->id, 'title' => 'Pengenalan Testing']);

        // Create assignment
        $assignmentResponse = $this->actingAs($lecturer)->post("/courses/{$course->id}/assignments", [
            'title' => 'Tugas Unit Test',
            'description' => 'Tulis 3 unit test.',
            'max_score' => 100,
            'due_at' => now()->addDays(7)->toDateTimeString(),
            'allow_late_submission' => 1,
            'is_published' => 1,
        ]);
        $assignmentResponse->assertRedirect("/courses/{$course->id}?tab=assignments");
        $this->assertDatabaseHas('assignments', ['course_id' => $course->id, 'title' => 'Tugas Unit Test']);
    }

    public function test_student_can_take_and_submit_quiz(): void
    {
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $this->assertNotNull($student);
        $this->assertNotNull($lecturer);
        $this->assertNotNull($course);

        // Create dedicated test quiz
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Kuis Uji Coba '.rand(1000, 9999),
            'duration_minutes' => 30,
            'max_attempts' => 5,
            'passing_score' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'text' => 'Berapakah 2 + 2?',
            'points' => 100,
            'sort_order' => 1,
        ]);

        $correctOption = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => '5',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        // Start attempt
        $startResponse = $this->actingAs($student)->post("/quizzes/{$quiz->id}/start");
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($attempt);
        $startResponse->assertRedirect("/quiz-attempts/{$attempt->id}");

        // Submit attempt with answers
        $submitResponse = $this->actingAs($student)->post("/quiz-attempts/{$attempt->id}/submit", [
            'answers' => [
                $question->id => $correctOption->id,
            ],
        ]);

        $submitResponse->assertRedirect("/quiz-attempts/{$attempt->id}/result");
        $attempt->refresh();
        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals(100.0, (float) $attempt->score);
    }

    public function test_quiz_can_handle_10_questions_with_points_totaling_100(): void
    {
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $this->assertNotNull($student);
        $this->assertNotNull($lecturer);
        $this->assertNotNull($course);

        // 1. Create quiz
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Kuis 10 Soal Evaluasi',
            'duration_minutes' => 45,
            'max_attempts' => 3,
            'passing_score' => 70,
            'is_published' => true,
        ]);

        // 2. Add 10 questions with varying initial points
        $questions = [];
        $correctAnswers = [];
        for ($i = 1; $i <= 10; $i++) {
            $q = Question::create([
                'quiz_id' => $quiz->id,
                'type' => 'multiple_choice',
                'text' => "Pertanyaan ke-{$i} tentang arsitektur perangkat lunak?",
                'points' => 5, // initially 5
                'sort_order' => $i,
            ]);

            $correctOpt = QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => "Pilihan Benar {$i}",
                'is_correct' => true,
                'sort_order' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $q->id,
                'option_text' => "Pilihan Salah {$i}",
                'is_correct' => false,
                'sort_order' => 2,
            ]);

            $questions[] = $q;
            $correctAnswers[$q->id] = $correctOpt->id;
        }

        $this->assertCount(10, $quiz->questions);

        // 3. Distribute points to total exactly 100
        $distributeResponse = $this->actingAs($lecturer)->post("/quizzes/{$quiz->id}/distribute-points");
        $distributeResponse->assertSessionHas('success');

        $quiz->load('questions');
        $this->assertEquals(100.0, (float) $quiz->questions->sum('points'));
        foreach ($quiz->questions as $qItem) {
            $this->assertEquals(10.0, (float) $qItem->points);
        }

        // 4. Student starts attempt
        $startResponse = $this->actingAs($student)->post("/quizzes/{$quiz->id}/start");
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($attempt);
        $startResponse->assertRedirect("/quiz-attempts/{$attempt->id}");

        // 5. Student submits answers: 8 correct, 2 omitted/wrong
        $submittedAnswers = [];
        for ($idx = 0; $idx < 8; $idx++) {
            $q = $questions[$idx];
            $submittedAnswers[$q->id] = $correctAnswers[$q->id];
        }

        $submitResponse = $this->actingAs($student)->post("/quiz-attempts/{$attempt->id}/submit", [
            'answers' => $submittedAnswers,
        ]);

        $submitResponse->assertRedirect("/quiz-attempts/{$attempt->id}/result");
        $attempt->refresh();

        $this->assertEquals('graded', $attempt->status);
        // 8 correct questions x 10 points = 80.00
        $this->assertEquals(80.0, (float) $attempt->score);
        $this->assertCount(10, $attempt->answers);

        // Result page is accessible and displays results
        $resultResponse = $this->actingAs($student)->get("/quiz-attempts/{$attempt->id}/result");
        $resultResponse->assertStatus(200);
        $resultResponse->assertSee('80');
        $resultResponse->assertSee('LULUS');
    }

    public function test_quiz_can_handle_50_questions_with_total_points_benchmarked_to_100(): void
    {
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        // 1. Create quiz
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Ujian Akhir 50 Soal',
            'duration_minutes' => 90,
            'max_attempts' => 2,
            'passing_score' => 75,
            'is_published' => true,
        ]);

        // 2. Batch generate 50 questions using the controller endpoint
        $batchResponse = $this->actingAs($lecturer)->post("/quizzes/{$quiz->id}/generate-template", [
            'count' => 50,
        ]);
        $batchResponse->assertSessionHas('success');

        $quiz->load('questions');
        $this->assertCount(50, $quiz->questions);

        // 3. Ensure points are evenly distributed to exactly 100 points (50 x 2.00 = 100)
        $this->assertEquals(100.0, (float) $quiz->questions->sum('points'));
        foreach ($quiz->questions as $qItem) {
            $this->assertEquals(2.0, (float) $qItem->points);
        }

        // 4. Student starts attempt
        $startResponse = $this->actingAs($student)->post("/quizzes/{$quiz->id}/start");
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($attempt);

        // 5. Student submits answers for 45 questions correctly (45 x 2 = 90 points)
        $submittedAnswers = [];
        $questions = $quiz->questions()->with('options')->get();
        for ($i = 0; $i < 45; $i++) {
            $q = $questions[$i];
            $correctOption = $q->options->firstWhere('is_correct', true);
            $submittedAnswers[$q->id] = $correctOption->id;
        }

        $submitResponse = $this->actingAs($student)->post("/quiz-attempts/{$attempt->id}/submit", [
            'answers' => $submittedAnswers,
        ]);

        $submitResponse->assertRedirect("/quiz-attempts/{$attempt->id}/result");
        $attempt->refresh();

        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals(90.0, (float) $attempt->score);
        $this->assertCount(50, $attempt->answers);
    }

    public function test_admin_can_access_admin_panel_while_student_is_forbidden(): void
    {
        $admin = User::where('email', 'admin@lms.test')->first();
        $student = User::where('email', 'mahasiswa@lms.test')->first();

        $this->assertNotNull($admin);
        $this->assertNotNull($student);

        // Admin access -> 200 OK
        $adminResponse = $this->actingAs($admin)->get('/admin/users');
        $adminResponse->assertStatus(200);

        // Student access -> 403 Forbidden
        $studentResponse = $this->actingAs($student)->get('/admin/users');
        $studentResponse->assertStatus(403);
    }

    public function test_ai_can_analyze_assignment_submission_with_gemini_service_mock(): void
    {
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Analisis AI',
            'description' => 'Jelaskan konsep arsitektur RESTful API.',
            'max_score' => 100,
            'due_at' => now()->addDays(5),
            'is_published' => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'RESTful API menggunakan metode HTTP seperti GET, POST, PUT, DELETE untuk mengelola resource.',
            'submitted_at' => now(),
        ]);

        $mockGemini = Mockery::mock(GeminiService::class);
        $mockGemini->shouldReceive('analyzeAssignmentSubmission')
            ->once()
            ->andReturn([
                'summary' => 'Mahasiswa menjelaskan metode HTTP pada RESTful API.',
                'analysis' => 'Jawaban relevan dengan instruksi dasar penugasan.',
                'suggested_score' => 88.5,
                'suggested_feedback' => 'Penjelasan baik, tingkatkan contoh implementasi.',
            ]);

        $this->app->instance(GeminiService::class, $mockGemini);

        // Student attempt -> 403 Forbidden
        $forbiddenResponse = $this->actingAs($student)->postJson("/submissions/{$submission->id}/ai-analyze");
        $forbiddenResponse->assertStatus(403);

        // Lecturer attempt -> 200 OK
        $response = $this->actingAs($lecturer)->postJson("/submissions/{$submission->id}/ai-analyze");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'suggested_score' => 88.5,
            ],
        ]);
    }

    public function test_ai_can_generate_quiz_questions_with_gemini_service_mock(): void
    {
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Kuis Otomasi AI',
            'duration_minutes' => 30,
            'max_attempts' => 2,
            'passing_score' => 70,
            'is_published' => true,
        ]);

        $mockGemini = Mockery::mock(GeminiService::class);
        $mockGemini->shouldReceive('generateQuizQuestions')
            ->once()
            ->with('Laravel Eloquent', 2, 'sedang', null)
            ->andReturn([
                [
                    'type' => 'multiple_choice',
                    'text' => 'Apa itu Eloquent ORM di Laravel?',
                    'options' => [
                        ['option_text' => 'Object Relational Mapper bawaan Laravel', 'is_correct' => true],
                        ['option_text' => 'Mesin caching', 'is_correct' => false],
                        ['option_text' => 'Template engine', 'is_correct' => false],
                        ['option_text' => 'Compiler PHP', 'is_correct' => false],
                    ],
                ],
                [
                    'type' => 'true_false',
                    'text' => 'Eloquent Model mewakili tabel dalam database.',
                    'tf_correct' => 'true',
                ],
            ]);

        $this->app->instance(GeminiService::class, $mockGemini);

        $response = $this->actingAs($lecturer)->post("/quizzes/{$quiz->id}/ai-generate-questions", [
            'topic' => 'Laravel Eloquent',
            'count' => 2,
            'difficulty' => 'sedang',
        ]);

        $response->assertSessionHas('success');
        $quiz->load('questions.options');

        $this->assertCount(2, $quiz->questions);
        $this->assertEquals(100.0, (float) $quiz->questions->sum('points'));
        $this->assertEquals('Apa itu Eloquent ORM di Laravel?', $quiz->questions[0]->text);
        $this->assertCount(4, $quiz->questions[0]->options);
        $this->assertTrue((bool) $quiz->questions[0]->options[0]->is_correct);
    }

    public function test_ai_can_summarize_and_evaluate_essay_and_lecturer_can_grade_it(): void
    {
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $lecturer = User::where('email', 'dosen@lms.test')->first();
        $course = Course::where('is_published', true)->first();

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Kuis Esai Analisis',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'passing_score' => 70,
            'is_published' => true,
        ]);

        $essayQuestion = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'essay',
            'text' => 'Jelaskan perbedaan mendasar antara monolithic dan microservices architecture.',
            'points' => 100,
            'sort_order' => 1,
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(15),
            'submitted_at' => now(),
            'score' => null,
            'status' => 'submitted',
        ]);

        $answer = QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $essayQuestion->id,
            'answer_text' => 'Monolithic menyatukan seluruh komponen dalam satu basis kode, sedangkan microservices membaginya menjadi layanan mandiri yang terdistribusi.',
        ]);

        // Mock Gemini
        $mockGemini = Mockery::mock(GeminiService::class);
        $mockGemini->shouldReceive('summarizeAndEvaluateEssay')
            ->once()
            ->andReturn([
                'summary' => 'Perbedaan kode tunggal vs terdistribusi mandiri.',
                'evaluation' => 'Penjelasan esensial telah tepat menjawab poin utama pertanyaan.',
                'suggested_score' => 90.0,
                'notes' => 'Tambahkan aspek skalabilitas dan deployment.',
            ]);

        $this->app->instance(GeminiService::class, $mockGemini);

        // AI evaluation endpoint
        $aiResponse = $this->actingAs($lecturer)->postJson("/quiz-answers/{$answer->id}/ai-evaluate");
        $aiResponse->assertStatus(200);
        $aiResponse->assertJson([
            'success' => true,
            'data' => [
                'suggested_score' => 90.0,
            ],
        ]);

        // Lecturer submits actual score for the essay
        $gradeResponse = $this->actingAs($lecturer)->post("/quiz-answers/{$answer->id}/grade-essay", [
            'score' => 90,
        ]);

        $gradeResponse->assertSessionHas('success');

        $answer->refresh();
        $this->assertEquals(90.0, (float) $answer->score);
        $this->assertTrue((bool) $answer->is_correct);

        $attempt->refresh();
        $this->assertEquals('graded', $attempt->status);
        $this->assertEquals(90.0, (float) $attempt->score);
    }
}
