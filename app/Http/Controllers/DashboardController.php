<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {   
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString();

        // Count the distinct attendance dates per department, course
        $distinctAttendanceDaysByDepartmentStudent = DB::table("student_attendances")
            ->join("students", "student_attendances.student_id", "=", "students.id")
            ->join("courses", "students.course_id", "=", "courses.id")
            ->join("departments", "courses.department_id", "=", "departments.id")
            ->leftJoin("holidays", DB::raw("DATE(student_attendances.created_at)"), "=", "holidays.date")
            ->select(
                "students.card_id as student_id",
                "courses.slug as course_slug",
                "courses.name as course_name",
                "departments.slug as department_slug",
                "departments.name as department_name",
                DB::raw("COUNT(DISTINCT DATE(student_attendances.created_at)) as distinct_attendance_days")
            )
            ->whereBetween("student_attendances.created_at", [$startDate, $endDate])
            ->where("holidays.date")
            ->groupBy("students.card_id", "courses.slug", "departments.slug", "courses.name", "departments.name")
            ->get();
    

        // Sum distinct attendance days per department
        $attendanceSummedByDepartment = $distinctAttendanceDaysByDepartmentStudent->groupBy('department_slug')->map(function ($group) {
            return $group->sum('distinct_attendance_days');
        });

        // Sum distinct attendance days per course
        $attendanceSummedByCourse = $distinctAttendanceDaysByDepartmentStudent->groupBy('course_slug')->map(function ($group) {
            return $group->sum('distinct_attendance_days');
        });

        return view(
            "admin.index",
            compact(
                "attendanceSummedByDepartment",
                "attendanceSummedByCourse",
            )
        );
    }

}
