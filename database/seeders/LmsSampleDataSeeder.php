<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseMember;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Grade;
use App\Models\LecturerProfile;
use App\Models\Material;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class LmsSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $dosen = User::where('email', 'dosen@lms.test')->first();
        $student = User::where('email', 'mahasiswa@lms.test')->first();
        $admin = User::where('email', 'admin@lms.test')->first();

        if (! $dosen || ! $student || ! $admin) {
            return;
        }

        // 1. Setup Profiles
        LecturerProfile::updateOrCreate(
            ['user_id' => $dosen->id],
            [
                'lecturer_number' => 'NIDN-198801152020',
                'academic_title' => 'M.Kom, Ph.D.',
                'study_program' => 'Teknik Informatika',
                'bio' => 'Dosen dan peneliti di bidang Rekayasa Perangkat Lunak dan Cloud Computing dengan pengalaman 10+ tahun.',
            ]
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $student->id],
            [
                'student_number' => 'NIM-20240801001',
                'study_program' => 'Teknik Informatika',
                'entry_year' => 2024,
                'gender' => 'male',
                'birth_date' => '2003-05-14',
                'address' => 'Jl. Pendidikan No. 42, Bandung',
            ]
        );

        // 2. Create Courses
        $coursesData = [
            [
                'code' => 'IF301-WEB',
                'name' => 'Pemrograman Web Modern (Laravel & Vue)',
                'description' => 'Mempelajari arsitektur web modern dengan Laravel 11/12, Blade templating, RESTful APIs, Tailwind CSS, dan integrasi frontend interaktif.',
                'thumbnail' => null,
                'is_published' => true,
                'created_by' => $dosen->id,
            ],
            [
                'code' => 'IF202-ALGO',
                'name' => 'Struktur Data & Algoritma Tingkat Lanjut',
                'description' => 'Eksplorasi mendalam struktur data pohon (Trees), Graphs, Dynamic Programming, dan optimasi algoritma penelusuran.',
                'thumbnail' => null,
                'is_published' => true,
                'created_by' => $dosen->id,
            ],
            [
                'code' => 'IF305-UX',
                'name' => 'Desain Interaksi & UI/UX Design System',
                'description' => 'Prinsip desain pengalaman pengguna yang berpusat pada manusia, typography, micro-interactions, dan pembuatan design system berskala besar.',
                'thumbnail' => null,
                'is_published' => true,
                'created_by' => $dosen->id,
            ],
            [
                'code' => 'IF401-CLOUD',
                'name' => 'Arsitektur Cloud & Sistem Terdistribusi',
                'description' => 'Konsep dasar skalabilitas aplikasi, kontainerisasi dengan Docker, caching, dan message queues di lingkungan cloud computing.',
                'thumbnail' => null,
                'is_published' => false,
                'created_by' => $dosen->id,
            ],
        ];

        $courses = [];
        foreach ($coursesData as $cData) {
            $courses[] = Course::updateOrCreate(['code' => $cData['code']], $cData);
        }

        $mainCourse = $courses[0]; // Pemrograman Web Modern
        $secondCourse = $courses[1]; // Struktur Data

        // 3. Enrollments (Course Members)
        CourseMember::updateOrCreate(
            ['course_id' => $mainCourse->id, 'user_id' => $dosen->id],
            ['member_role' => 'lecturer', 'joined_at' => now()->subDays(30)]
        );
        CourseMember::updateOrCreate(
            ['course_id' => $mainCourse->id, 'user_id' => $student->id],
            ['member_role' => 'student', 'joined_at' => now()->subDays(25)]
        );

        CourseMember::updateOrCreate(
            ['course_id' => $secondCourse->id, 'user_id' => $dosen->id],
            ['member_role' => 'lecturer', 'joined_at' => now()->subDays(20)]
        );
        CourseMember::updateOrCreate(
            ['course_id' => $secondCourse->id, 'user_id' => $student->id],
            ['member_role' => 'student', 'joined_at' => now()->subDays(18)]
        );

        // 4. Materials
        Material::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Pengenalan Arsitektur MVC dan Routing di Laravel'],
            [
                'created_by' => $dosen->id,
                'description' => 'Pemahaman fondasi arsitektur Model-View-Controller dan penanganan HTTP request di Laravel.',
                'type' => 'text',
                'content' => "## Konsep MVC di Laravel\n\nLaravel menggunakan pola arsitektur **Model-View-Controller (MVC)** untuk memisahkan tanggung jawab kode:\n\n- **Model**: Mengelola interaksi basis data menggunakan Eloquent ORM.\n- **View**: Menyajikan antarmuka visual kepada pengguna menggunakan Blade template engine.\n- **Controller**: Mengendalikan logika alur kerja, validasi request, dan koordinasi antara model dan view.\n\n### Best Practice Routing\n\nSelalu gunakan **named routes** agar memudahkan pemeliharaan rute ketika path URL berubah di kemudian hari.",
                'sort_order' => 1,
                'is_published' => true,
            ]
        );

        Material::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Video Kuliah: Eloquent ORM & Relasi Antar Model'],
            [
                'created_by' => $dosen->id,
                'description' => 'Rekaman sesi penjelasan relasi HasMany, BelongsTo, ManyToMany, dan teknik Eager Loading.',
                'type' => 'video',
                'external_url' => 'https://www.youtube.com/watch?v=MFh0ADGZ3dA',
                'content' => 'Tonton video panduan implementasi relasi Eloquent di Laravel.',
                'sort_order' => 2,
                'is_published' => true,
            ]
        );

        Material::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Modul PDF: Panduan Best Practices RESTful API'],
            [
                'created_by' => $dosen->id,
                'description' => 'Dokumentasi standar HTTP response codes, format JSON Resource, dan autentikasi token.',
                'type' => 'link',
                'external_url' => 'https://laravel.com/docs/eloquent-resources',
                'content' => 'Pelajari dokumentasi resmi Laravel Eloquent API Resources.',
                'sort_order' => 3,
                'is_published' => true,
            ]
        );

        // 5. Assignments
        $assignment1 = Assignment::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Tugas 1: Pembuatan Sistem Otentikasi & RBAC'],
            [
                'created_by' => $dosen->id,
                'description' => 'Buatlah implementasi otentikasi kustom dengan peran pengguna (Admin, Dosen, Mahasiswa) lengkap dengan validasi request, middleware, dan antarmuka login yang responsif.',
                'max_score' => 100,
                'available_at' => now()->subDays(14),
                'due_at' => now()->subDays(2),
                'allow_late_submission' => true,
                'is_published' => true,
            ]
        );

        $assignment2 = Assignment::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Tugas 2: Implementasi REST API CRUD Course & Material'],
            [
                'created_by' => $dosen->id,
                'description' => 'Kembangkan endpoint API untuk manajemen materi dan penugasan menggunakan API Resources dan Form Request Validation. Dokumentasikan dengan Postman collection.',
                'max_score' => 100,
                'available_at' => now()->subDays(3),
                'due_at' => now()->addDays(5),
                'allow_late_submission' => false,
                'is_published' => true,
            ]
        );

        // Submission for Assignment 1
        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment1->id, 'student_id' => $student->id],
            [
                'content' => 'Saya telah menyelesaikan implementasi sistem autentikasi dan RBAC dengan middleware. Seluruh endpoint telah dites dan berjalan normal. Kode repository telah diunggah ke GitHub.',
                'submitted_at' => now()->subDays(3),
                'score' => 95.00,
                'feedback' => 'Kerja yang sangat rapi! Struktur middleware dan pemisahan kontroler sudah sangat baik. Pertahankan kualitas kodenya.',
                'graded_by' => $dosen->id,
                'graded_at' => now()->subDays(1),
                'is_late' => false,
            ]
        );

        // 6. Quizzes & Questions
        $quiz = Quiz::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Kuis 1: Konsep Dasar Laravel & Arsitektur Web'],
            [
                'created_by' => $dosen->id,
                'description' => 'Kuis pemahaman konsep MVC, Eloquent ORM, Blade templating, dan HTTP request handling.',
                'duration_minutes' => 30,
                'max_attempts' => 3,
                'passing_score' => 70.00,
                'available_at' => now()->subDays(7),
                'due_at' => now()->addDays(7),
                'shuffle_questions' => true,
                'is_published' => true,
            ]
        );

        // Question 1
        $q1 = Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Fitur apa di Laravel yang digunakan untuk mendefinisikan skema tabel basis data secara terprogram?'],
            [
                'type' => 'multiple_choice',
                'points' => 25,
                'sort_order' => 1,
            ]
        );
        QuestionOption::updateOrCreate(['question_id' => $q1->id, 'option_text' => 'Migrations'], ['is_correct' => true, 'sort_order' => 1]);
        QuestionOption::updateOrCreate(['question_id' => $q1->id, 'option_text' => 'Seeders'], ['is_correct' => false, 'sort_order' => 2]);
        QuestionOption::updateOrCreate(['question_id' => $q1->id, 'option_text' => 'Factories'], ['is_correct' => false, 'sort_order' => 3]);
        QuestionOption::updateOrCreate(['question_id' => $q1->id, 'option_text' => 'Middlewares'], ['is_correct' => false, 'sort_order' => 4]);

        // Question 2
        $q2 = Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Perintah Artisan manakah yang digunakan untuk membuat Controller baru di Laravel?'],
            [
                'type' => 'multiple_choice',
                'points' => 25,
                'sort_order' => 2,
            ]
        );
        QuestionOption::updateOrCreate(['question_id' => $q2->id, 'option_text' => 'php artisan make:controller ControllerName'], ['is_correct' => true, 'sort_order' => 1]);
        QuestionOption::updateOrCreate(['question_id' => $q2->id, 'option_text' => 'php artisan new:controller ControllerName'], ['is_correct' => false, 'sort_order' => 2]);
        QuestionOption::updateOrCreate(['question_id' => $q2->id, 'option_text' => 'php artisan create:controller ControllerName'], ['is_correct' => false, 'sort_order' => 3]);
        QuestionOption::updateOrCreate(['question_id' => $q2->id, 'option_text' => 'php artisan build:controller ControllerName'], ['is_correct' => false, 'sort_order' => 4]);

        // Question 3
        $q3 = Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Dalam Blade template, sintaks mana yang digunakan untuk menampilkan data dengan perlindungan XSS (escaping)?'],
            [
                'type' => 'multiple_choice',
                'points' => 25,
                'sort_order' => 3,
            ]
        );
        QuestionOption::updateOrCreate(['question_id' => $q3->id, 'option_text' => '{{ $variable }}'], ['is_correct' => true, 'sort_order' => 1]);
        QuestionOption::updateOrCreate(['question_id' => $q3->id, 'option_text' => '{!! $variable !!}'], ['is_correct' => false, 'sort_order' => 2]);
        QuestionOption::updateOrCreate(['question_id' => $q3->id, 'option_text' => '<% $variable %>'], ['is_correct' => false, 'sort_order' => 3]);
        QuestionOption::updateOrCreate(['question_id' => $q3->id, 'option_text' => '[[ $variable ]]'], ['is_correct' => false, 'sort_order' => 4]);

        // Question 4
        $q4 = Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Secara default, Laravel menggunakan token CSRF untuk melindungi form HTTP POST dari serangan lintas situs.'],
            [
                'type' => 'true_false',
                'points' => 25,
                'sort_order' => 4,
            ]
        );
        QuestionOption::updateOrCreate(['question_id' => $q4->id, 'option_text' => 'Benar (True)'], ['is_correct' => true, 'sort_order' => 1]);
        QuestionOption::updateOrCreate(['question_id' => $q4->id, 'option_text' => 'Salah (False)'], ['is_correct' => false, 'sort_order' => 2]);

        // Quiz Attempt for student
        $attempt = QuizAttempt::updateOrCreate(
            ['quiz_id' => $quiz->id, 'student_id' => $student->id, 'attempt_number' => 1],
            [
                'started_at' => now()->subDays(2)->subMinutes(25),
                'submitted_at' => now()->subDays(2),
                'score' => 100.00,
                'status' => 'graded',
            ]
        );

        // Answers for attempt
        $q1CorrectOpt = QuestionOption::where('question_id', $q1->id)->where('is_correct', true)->first();
        if ($q1CorrectOpt) {
            QuizAnswer::updateOrCreate(
                ['quiz_attempt_id' => $attempt->id, 'question_id' => $q1->id],
                ['question_option_id' => $q1CorrectOpt->id, 'is_correct' => true, 'score' => 25]
            );
        }

        $q2CorrectOpt = QuestionOption::where('question_id', $q2->id)->where('is_correct', true)->first();
        if ($q2CorrectOpt) {
            QuizAnswer::updateOrCreate(
                ['quiz_attempt_id' => $attempt->id, 'question_id' => $q2->id],
                ['question_option_id' => $q2CorrectOpt->id, 'is_correct' => true, 'score' => 25]
            );
        }

        // 7. Grades
        Grade::updateOrCreate(
            ['course_id' => $mainCourse->id, 'student_id' => $student->id, 'component' => 'Tugas 1: Otentikasi'],
            ['score' => 95.00, 'notes' => 'Sangat memuaskan', 'graded_by' => $dosen->id]
        );
        Grade::updateOrCreate(
            ['course_id' => $mainCourse->id, 'student_id' => $student->id, 'component' => 'Kuis 1: Konsep Dasar'],
            ['score' => 100.00, 'notes' => 'Nilai sempurna', 'graded_by' => $dosen->id]
        );

        // 8. Announcements
        Announcement::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Selamat Datang di Semester Ganjil 2026/2027!'],
            [
                'created_by' => $dosen->id,
                'content' => "Halo rekan-rekan mahasiswa,\n\nSelamat datang di mata kuliah Pemrograman Web Modern. Silakan cermati silabus dan materi yang sudah diunggah pada tab Materi. Pastikan untuk selalu memeriksa tenggat waktu pengumpulan tugas pada tab Tugas.\n\nSalam hangat,\nTim Dosen Pengampu",
                'published_at' => now()->subDays(25),
            ]
        );

        // 9. Discussions & Replies
        $discussion = Discussion::updateOrCreate(
            ['course_id' => $mainCourse->id, 'title' => 'Tanya Jawab: Solusi Error SQLSTATE pada Foreign Key Migration'],
            [
                'user_id' => $student->id,
                'content' => 'Permisi Pak Dosen dan teman-teman, saat menjalankan migration foreign key pada tabel yang saling berelasi, bagaimana urutan eksekusi migration yang tepat agar tidak muncul pesan error table not found?',
                'is_locked' => false,
            ]
        );

        DiscussionReply::updateOrCreate(
            ['discussion_id' => $discussion->id, 'content' => 'Halo! Pastikan tabel induk (tabel yang direferensikan) dibuat lebih dulu sebelum tabel anak yang memuat foreign key. Laravel mengurutkan eksekusi migration berdasarkan timestamp nama filenya.'],
            [
                'user_id' => $dosen->id,
            ]
        );
    }
}
