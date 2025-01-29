<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {   
        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->toDateString();

        // Fetch Students per Course (Total Students per Course)
        $studentsPerCourse = Course::withCount("students")->get();

        // Total Number of Students across all courses
        $totalStudents = DB::table("students")->count();

        $studentsPresentTodayByCourse = DB::table("student_attendances")
            ->join(
                "students",
                "student_attendances.student_id",
                "=",
                "students.id"
            )
            ->join("courses", "students.course_id", "=", "courses.id")
            ->select(
                "courses.name as course_name",
                DB::raw("COUNT(student_attendances.id) as attendance_count")
            )
            ->whereBetween("student_attendances.created_at", [
                $startDate,
                $endDate,
            ])
            ->groupBy("course_name")
            ->get();

        // Number of Students Present Today in Total
        $studentsPresentToday = $studentsPresentTodayByCourse->sum(
            "attendance_count"
        );

        return view(
            "admin.index",
            compact(
                "studentsPresentToday",
                "studentsPresentTodayByCourse",
            )
        );
    }

}
