<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load('role');

        $data = [
            'user' => $user,
        ];

        if ($user->isAdmin()) {
            $data['totalUsers'] = User::count();
            $data['totalLecturers'] = User::whereHas('role', fn ($q) => $q->where('slug', 'dosen'))->count();
            $data['totalStudents'] = User::whereHas('role', fn ($q) => $q->where('slug', 'mahasiswa'))->count();
            $data['totalCourses'] = Course::count();
            $data['publishedCourses'] = Course::where('is_published', true)->count();
            $data['recentUsers'] = User::with('role')->latest()->take(5)->get();
            $data['recentCourses'] = Course::with('creator')->latest()->take(5)->get();
        } elseif ($user->isDosen()) {
            $myCourses = Course::where('created_by', $user->id)
                ->withCount(['members as student_count' => fn ($q) => $q->where('member_role', 'student')])
                ->withCount('materials', 'assignments', 'quizzes')
                ->latest()
                ->get();

            $courseIds = $myCourses->pluck('id');

            $data['myCourses'] = $myCourses;
            $data['totalCourses'] = $myCourses->count();
            $data['totalStudents'] = Course::where('created_by', $user->id)
                ->join('course_members', 'courses.id', '=', 'course_members.course_id')
                ->where('course_members.member_role', 'student')
                ->distinct('course_members.user_id')
                ->count('course_members.user_id');

            $data['pendingSubmissions'] = AssignmentSubmission::whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->whereNull('score')
                ->with(['assignment.course', 'student'])
                ->latest('submitted_at')
                ->take(5)
                ->get();

            $data['activeQuizzes'] = Quiz::whereIn('course_id', $courseIds)
                ->where('is_published', true)
                ->with('course')
                ->latest()
                ->take(5)
                ->get();
        } else {
            // Mahasiswa
            $enrolledCourses = $user->enrolledCourses()
                ->with('creator')
                ->withCount('materials', 'assignments', 'quizzes')
                ->get();

            $enrolledCourseIds = $enrolledCourses->pluck('id');

            $data['enrolledCourses'] = $enrolledCourses;
            $data['totalEnrolledCourses'] = $enrolledCourses->count();

            // Upcoming assignments due
            $data['upcomingAssignments'] = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('due_at', '>=', now())
                ->with(['course', 'submissions' => fn ($q) => $q->where('student_id', $user->id)])
                ->orderBy('due_at', 'asc')
                ->take(5)
                ->get();

            // Available quizzes
            $data['availableQuizzes'] = Quiz::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->with(['course', 'attempts' => fn ($q) => $q->where('student_id', $user->id)])
                ->latest()
                ->take(5)
                ->get();

            // Recent Announcements
            $data['recentAnnouncements'] = Announcement::whereIn('course_id', $enrolledCourseIds)
                ->with(['course', 'creator'])
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        return view('dashboard.index', $data);
    }
}
