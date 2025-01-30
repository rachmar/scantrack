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
        $startDate = $request->get("start_date", Carbon::today()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->toDateString());

        // Fetch student record
        $student = Student::where('card_id', $request->student_id)->first();

        // Convert student's schedule into an array
        $scheduleDays = $student->schedule ?? [];

        // Map days of the week to their respective codes
        $dayMap = [
            'M'  => 1,  // Monday
            'T'  => 2,  // Tuesday
            'W'  => 3,  // Wednesday
            'TH' => 4,  // Thursday
            'F'  => 5,  // Friday
            'S'  => 6,  // Saturday
        ];

        // Define the holiday array
        $holidays = [
            "2025-01-14" => 'Event 1',
            "2025-01-15" => 'Event 2',
        ];

        // Get all valid scheduled dates for the student within the date range
        $scheduledDates = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            // Get the day number for the current date (1=Monday, 2=Tuesday, etc.)
            $dayNumber = $currentDate->dayOfWeek; 

            // Adjust to the correct value since Carbon's dayOfWeek starts from 0 (Sunday)
            // Convert day number to match the student's schedule (e.g., 0 = Sunday, 1 = Monday)
            $dayNumber = ($dayNumber == 0) ? 7 : $dayNumber;

            // Check if this day is in the student's schedule
            if (in_array(array_search($dayNumber, $dayMap), $scheduleDays)) {
                // Exclude the date if it's a holiday
                if (!isset($holidays[$currentDate->toDateString()])) {
                    $scheduledDates[] = $currentDate->toDateString();
                }
            }

            // Move to the next day
            $currentDate->addDay();
        }

        // Calculate number of school days based on the schedule
        $numOfSchoolDays = count($scheduledDates);

        // Get total attendance count within the range
        $studentAttendance = DB::table("student_attendances")
        ->where("student_attendances.student_id", $student->id)
        ->whereBetween("student_attendances.created_at", [$startDate, $endDate])
        ->pluck("created_at")
        ->map(function ($date) {
            return Carbon::parse($date)->toDateString();
        })
        ->toArray();

        // Exclude holidays from the attendance list
        $studentAttendance = array_diff($studentAttendance, array_keys($holidays));

        // Calculate number of absences (scheduled days - attended days)
        $numOfStudentAttendance = count($studentAttendance);

        $numOfAbsences = $numOfSchoolDays - $numOfStudentAttendance;

        // Check if the student is present today
        $isPresentToday = in_array(Carbon::today()->toDateString(), $studentAttendance);

        // Fetch attendance data for charts
        $dailyAttendanceQuery = DB::table("student_attendances")
        ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
        ->where("student_attendances.student_id", $student->id)
        ->whereBetween("created_at", [$startDate, $endDate])
        ->groupBy("date")
        ->orderBy("date");

        $dailyAttendance = $dailyAttendanceQuery->get();

        // Filter out holidays from the daily attendance data
        $dailyAttendance = $dailyAttendance->filter(function ($attendance) use ($holidays) {
        return !isset($holidays[$attendance->date]);
        });

        // Prepare Daily Attendance Data for Chart.js
        $dailyLabels = $dailyAttendance->pluck("date")->toArray();
        $dailyValues = $dailyAttendance->pluck("count")->toArray();

        $dailyAttendanceEvents = array_map(function ($date, $value) {
            return [
                'title' => "Present",
                'start' => $date,
                'backgroundColor' => '#008000', // Red color for absence,
            ];
        }, $dailyLabels, $dailyValues);

        // Create Absence Events (where the student was scheduled but not present)
        $absenceEvents = [];
        foreach (array_diff($scheduledDates, $studentAttendance) as $absenceDate) {
            $absenceEvents[] = [
                'title' => 'Absent',
                'start' => $absenceDate,
                'backgroundColor' => '#800000', // Red color for absence,
            ];
        }

        // Create Holiday Events
        $holidayEvents = [];
        foreach ($holidays as $holidayDate => $holidayName) {
            // If the holiday falls within the selected date range
            if ($holidayDate >= $startDate && $holidayDate <= $endDate) {
                $holidayEvents[] = [
                    'title' => $holidayName,
                    'start' => $holidayDate,
                    'backgroundColor' => '#808000', // Red color for holiday
                ];
            }
        }

        // Fetch monthly attendance data
        $monthlyAttendanceQuery = DB::table("student_attendances")
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where("student_attendances.student_id", $student->id)
            ->whereBetween("created_at", [$startDate, $endDate])
            ->groupBy("month")
            ->orderBy("month");

        $monthlyAttendance = $monthlyAttendanceQuery->get();

        // Prepare Monthly Attendance Data for Chart.js
        $monthlyLabels = $monthlyAttendance->pluck("month")->map(function ($month) {
            return date("M", strtotime($month));
        })->toArray();

        $monthlyValues = $monthlyAttendance->pluck("count")->toArray();


        return view(
            "admin.student_reports",
            compact(
                "student",
                "isPresentToday",
                "numOfStudentAttendance",
                "numOfSchoolDays",
                "numOfAbsences",
                "absenceEvents",
                "dailyAttendanceEvents",
                "monthlyLabels",
                "monthlyValues",
                "holidayEvents"
            )
        );
    }

}
