<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function schoolReports(Request $request)
    {
        // Get today's date for both start and end date
        $startDate = $request->get("start_date",Carbon::today()->toDateString());
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
                "courses.slug as course_name",
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

              // Prepare Course Enrollment Data for Chart.js
        $attendanceByCourseLabels = $attendanceByCourse
            ->pluck("course_name")
            ->toArray(); // Course names

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
            "courses.slug as course_name",
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
            "admin.school_reports",
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



    public function studentReports(Request $request)
    {   
        // Get today's date for both start and end date
        $startDate = $request->get("start_date",Carbon::today()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->toDateString());

        $student = Student::where('card_id', $request->student_id)->first();

        $studentAttendance = DB::table("student_attendances")
            ->join(
                "students",
                "student_attendances.student_id",
                "=",
                "students.id"
            )
            ->select(
                DB::raw("COUNT(student_attendances.id) as attendance_count")
            )
            ->where("student_attendances.student_id", $student->id ?? 0) // Specific student ID
            ->whereBetween("student_attendances.created_at", [
                $startDate,
                $endDate,
            ])
            ->first();

        // Number of Students Present Today for the specific student
        $numofStudentAttendance = $studentAttendance->attendance_count;

        $studentAttendanceToday = DB::table("student_attendances")
            ->where("student_attendances.student_id", $student->id ?? 0) // Specific student ID
            ->whereDate("student_attendances.created_at", today()) // Check for today's date
            ->exists(); // Return true if the student has an attendance record for today

        // Check if the student is present today
        $isPresentToday = $studentAttendanceToday ? true : false;

       // Fetch attendance data for a specific student
        $dailyAttendanceQuery = DB::table("student_attendances")
        ->selectRaw("DATE(created_at) as date, COUNT(*) as count") // Group by date
        ->where("student_attendances.student_id", $student->id ?? 0) // Filter by specific student ID
        ->whereBetween("created_at", [$startDate, $endDate]) // Apply date range filter
        ->groupBy("date")
        ->orderBy("date");

        $dailyAttendance = $dailyAttendanceQuery->get();

        // Prepare Daily Attendance Data for Chart.js
        $dailyLabels = $dailyAttendance->pluck("date")->toArray(); // Dates (e.g., '2025-01-01')
        $dailyValues = $dailyAttendance->pluck("count")->toArray(); // Counts for each date
        
        $dailyAttendanceEvents = array_map(function ($date, $value) {
            return [
                'title' => "Present: $value",
                'start' => $date,
            ];
        }, $dailyLabels, $dailyValues);

        // Fetch monthly attendance data
        $monthlyAttendanceQuery = DB::table("student_attendances")
        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count") // Group by month
        ->where("student_attendances.student_id", $student->id ?? 0) // Filter by specific student ID
        ->whereBetween("created_at", [$startDate, $endDate]) // Apply date range filter
        ->groupBy("month")
        ->orderBy("month");

        $monthlyAttendance = $monthlyAttendanceQuery->get();

        // Prepare Monthly Attendance Data for Chart.js
        $monthlyLabels = $monthlyAttendance->pluck("month")->map(function ($month) {
        return date("M", strtotime($month)); // Convert 'YYYY-MM' to 'Mon' (e.g., '2025-01' to 'Jan')
        })->toArray();
        $monthlyValues = $monthlyAttendance->pluck("count")->toArray(); // Counts for each month


        return view(
            "admin.student_reports",
            compact(
                "student",
                "isPresentToday",
                "numofStudentAttendance",
                "dailyAttendanceEvents",
                "monthlyLabels",
                "monthlyValues"
            )
        );

    }
}
