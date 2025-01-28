<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function reports(Request $request)
    {
        // Get today's date for both start and end date
        $startDate = $request->get(
            "start_date",
            Carbon::today()->toDateString()
        );
        $endDate = $request->get("end_date", Carbon::today()->toDateString());

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


        // Prepare Course Enrollment Data for Chart.js
        $attendanceByCourseLabels = $studentsPerCourse
            ->pluck("name")
            ->toArray(); // Course names

        // Fetch attendance by course, optionally filtered by date range
        $attendanceByCourseQuery = DB::table("student_attendances")
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
            ->groupBy("course_name");

        // Apply date range filter if provided
        $attendanceByCourseQuery->whereBetween(
            "student_attendances.created_at",
            [$startDate, $endDate]
        );

        $attendanceByCourse = $attendanceByCourseQuery->get();

        // Prepare Attendance by Course Data for Chart.js
        $attendanceByCourseValues = $attendanceByCourse
            ->pluck("attendance_count")
            ->toArray();

        // Fetch daily attendance data, optionally filtered by date range
        $attendanceQuery = DB::table("student_attendances")
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy("date");

        // Apply date range filter if provided
        $attendanceQuery->whereBetween("created_at", [$startDate, $endDate]);

        $attendance = $attendanceQuery->get();

        // Prepare Daily Attendance Data for Chart.js
        $attendanceLabels = $attendance->pluck("date")->toArray();
        $attendanceValues = $attendance->pluck("count")->toArray();



        // Fetch Students Absent Today by Course (Total Absentees per Course)
// Fetch Courses with High Absentee Rates (Above 80%)
$coursesWithHighAbsenteeRates = DB::table("students")
->join("courses", "students.course_id", "=", "courses.id")
->leftJoin(
    "student_attendances",
    function ($join) use ($startDate, $endDate) {
        $join->on("students.id", "=", "student_attendances.student_id")
            ->whereBetween("student_attendances.created_at", [$startDate, $endDate]);
    }
)
->select(
    "courses.name as course_name",
    DB::raw("COUNT(DISTINCT  students.id) as total_students"),
    DB::raw("COUNT(DISTINCT  student_attendances.student_id) as attendance_count"),
    DB::raw("COUNT(DISTINCT  students.id) - COUNT(DISTINCT student_attendances.student_id) as absent_count"),
    DB::raw("ROUND((COUNT(DISTINCT students.id) - COUNT(DISTINCT student_attendances.student_id)) / COUNT(DISTINCT students.id) * 100, 2) as absentee_rate")
    )
->groupBy("course_name")
->get();

$coursesWithHighAbsenteeRateLabels = $coursesWithHighAbsenteeRates->pluck('course_name');
    $coursesWithHighAbsenteeRateTotalStudents = $coursesWithHighAbsenteeRates->pluck('total_students');
    $coursesWithHighAbsenteeRateAttendanceCounts = $coursesWithHighAbsenteeRates->pluck('attendance_count');
    $coursesWithHighAbsenteeRateAbsentCounts = $coursesWithHighAbsenteeRates->pluck('absent_count');
    $coursesWithHighAbsenteeRateValues  = $coursesWithHighAbsenteeRates->pluck('absentee_rate');

        return view(
            "reports",
            compact(
                "studentsPerCourse",
                "attendanceByCourseLabels",
                "attendanceByCourseValues",
                "attendanceLabels",
                "attendanceValues",
                "studentsPresentToday",
                "startDate",
                "endDate",
                "studentsPresentTodayByCourse",
                'coursesWithHighAbsenteeRateLabels',
        'coursesWithHighAbsenteeRateTotalStudents',
        'coursesWithHighAbsenteeRateAttendanceCounts',
        'coursesWithHighAbsenteeRateAbsentCounts',
        'coursesWithHighAbsenteeRateValues'
            )
        );
    }
}
