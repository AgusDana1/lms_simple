<?php

use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Public & Guest Routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create')->middleware('role:admin,dosen');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store')->middleware('role:admin,dosen');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit')->middleware('role:admin,dosen');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('role:admin,dosen');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('role:admin,dosen');

    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('/courses/{course}/unenroll', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    Route::post('/courses/{course}/members', [CourseController::class, 'addMember'])->name('courses.members.add')->middleware('role:admin,dosen');
    Route::delete('/courses/{course}/members/{member}', [CourseController::class, 'removeMember'])->name('courses.members.remove')->middleware('role:admin,dosen');

    // Materials
    Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('materials.store')->middleware('role:admin,dosen');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update')->middleware('role:admin,dosen');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy')->middleware('role:admin,dosen');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // Assignments
    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->name('assignments.store')->middleware('role:admin,dosen');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update')->middleware('role:admin,dosen');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy')->middleware('role:admin,dosen');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('submissions.grade')->middleware('role:admin,dosen');
    Route::get('/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('submissions.download');

    // Quizzes
    Route::post('/courses/{course}/quizzes', [QuizController::class, 'store'])->name('quizzes.store')->middleware('role:admin,dosen');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update')->middleware('role:admin,dosen');
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy')->middleware('role:admin,dosen');
    Route::get('/quizzes/{quiz}/questions', [QuizController::class, 'questions'])->name('quizzes.questions')->middleware('role:admin,dosen');
    Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store')->middleware('role:admin,dosen');
    Route::delete('/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('questions.destroy')->middleware('role:admin,dosen');

    Route::post('/quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/quiz-attempts/{attempt}', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quiz-attempts/{attempt}/submit', [QuizController::class, 'submitAttempt'])->name('quizzes.submit');
    Route::get('/quiz-attempts/{attempt}/result', [QuizController::class, 'result'])->name('quizzes.result');

    // Grades
    Route::get('/courses/{course}/grades', [GradeController::class, 'index'])->name('grades.index')->middleware('role:admin,dosen');
    Route::post('/courses/{course}/grades', [GradeController::class, 'store'])->name('grades.store')->middleware('role:admin,dosen');
    Route::get('/my-grades', [GradeController::class, 'myGrades'])->name('grades.my-grades');

    // Announcements
    Route::post('/courses/{course}/announcements', [AnnouncementController::class, 'store'])->name('announcements.store')->middleware('role:admin,dosen');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy')->middleware('role:admin,dosen');

    // Discussions
    Route::post('/courses/{course}/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{discussion}/reply', [DiscussionController::class, 'reply'])->name('discussions.reply');
    Route::patch('/discussions/{discussion}/lock', [DiscussionController::class, 'toggleLock'])->name('discussions.lock')->middleware('role:admin,dosen');
    Route::delete('/discussions/{discussion}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');

    // Admin Routes
    Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::patch('/courses/{course}/toggle-publish', [AdminCourseController::class, 'togglePublish'])->name('courses.toggle-publish');
    });
});
