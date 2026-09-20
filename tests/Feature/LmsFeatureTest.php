<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Str;
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
        $code = 'TEST-' . strtoupper(Str::random(8));
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
            'title' => 'Kuis Uji Coba ' . rand(1000, 9999),
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
}
