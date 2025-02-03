<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Holiday;
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
        // Get today's date for both start and end date, default to whole month if not provided
        $startDate = $request->get("start_date", Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->endOfMonth()->toDateString());

        // Fetch student record
        $student = Student::where('card_id', $request->student_id)->first() ?? Student::first();

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

        // Fetch holidays within the date range
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('name', 'date')
            ->toArray();

        // Get all valid scheduled dates for the student within the date range
        $scheduledDates = $this->getScheduledDates($startDate, $endDate, $scheduleDays, $dayMap, $holidays);

        // Calculate number of school days based on the schedule
        $numOfSchoolDays = count($scheduledDates);

        // Define the common query logic for attendance
        $attendanceQuery = DB::table("student_attendances")
            ->where("student_attendances.student_id", $student->id)
            ->whereBetween("student_attendances.created_at", [$startDate, $endDate]);

        // Get attendance counts per day (allowing multiple entries)
        $studentAttendance = $this->getAttendanceCounts($attendanceQuery, $holidays);

        // Get all attendance records per day (listing entries)
        $studentAttendanceTable = $this->getAttendanceRecords($attendanceQuery, $holidays);

        // Calculate total attendance and absences
        $numOfStudentAttendance = count($studentAttendance);
        $numOfAbsences = $numOfSchoolDays - $numOfStudentAttendance;

        $absenteeismRate = round((($numOfAbsences / $numOfSchoolDays) * 100), 2);

        // Check if the student is present today
        $isPresentToday = isset($studentAttendance[Carbon::today()->toDateString()]);

        // Prepare attendance data for charts
        $dailyLabels = array_keys($studentAttendance);
        $dailyValues = array_values($studentAttendance);

        $dailyAttendanceEvents = $this->prepareAttendanceEvents($studentAttendance, '#008000');
        $absenceEvents = $this->prepareAbsenceEvents($scheduledDates, array_keys($studentAttendance), '#800000');
        $holidayEvents = $this->prepareHolidayEvents($holidays, $startDate, $endDate);

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
                "holidayEvents",
                "studentAttendanceTable",
                "startDate",
                "endDate",
                "absenteeismRate"
            )
        );
    }

    // Extracted function to get the scheduled dates
    private function getScheduledDates($startDate, $endDate, $scheduleDays, $dayMap, $holidays)
    {
        $scheduledDates = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            $dayNumber = $currentDate->dayOfWeek;
            $dayNumber = ($dayNumber == 0) ? 7 : $dayNumber;

            if (in_array(array_search($dayNumber, $dayMap), $scheduleDays)) {
                if (!isset($holidays[$currentDate->toDateString()])) {
                    $scheduledDates[] = $currentDate->toDateString();
                }
            }

            $currentDate->addDay();
        }

        return $scheduledDates;
    }

    // Extracted function to get the attendance counts
    private function getAttendanceCounts($attendanceQuery, $holidays)
    {
        // Clone and get attendance counts per day
        $attendanceData = $attendanceQuery->clone()
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy("date")
            ->pluck("count", "date")
            ->toArray();

        // Exclude holidays from attendance counts per day
        foreach ($holidays as $holidayDate => $event) {
            if (isset($attendanceData[$holidayDate])) {
                unset($attendanceData[$holidayDate]);
            }
        }
        
        return $attendanceData;
    }

    // Extracted function to get the attendance records
    private function getAttendanceRecords($attendanceQuery, $holidays)
    {
        // Clone and get all attendance records per day
        $attendanceData = $attendanceQuery->clone()
            ->select("id", "created_at", "status")
            ->orderBy("created_at", "asc")
            ->get()
            ->groupBy(function ($entry) {
                return \Carbon\Carbon::parse($entry->created_at)->format('Y-m-d');
            });

        // Exclude holidays from attendance records per day
        foreach ($holidays as $holidayDate => $event) {
            if (isset($attendanceData[$holidayDate])) {
                unset($attendanceData[$holidayDate]);
            }
        }

        return $attendanceData->reverse();
    }

    // Extracted function to prepare attendance events for charts
    private function prepareAttendanceEvents($attendanceData, $color)
    {
        $events = [];
        foreach ($attendanceData as $date => $count) {
            $events[] = [
                'title' => "Present ($count)",
                'start' => $date,
                'backgroundColor' => $color,
            ];
        }

        return $events;
    }

    // Extracted function to prepare absence events for charts
    private function prepareAbsenceEvents($scheduledDates, $attendanceDates, $color)
    {
        $events = [];
        foreach (array_diff($scheduledDates, $attendanceDates) as $absenceDate) {
            $events[] = [
                'title' => 'Absent',
                'start' => $absenceDate,
                'backgroundColor' => $color,
            ];
        }

        return $events;
    }

    // Extracted function to prepare holiday events for charts
    private function prepareHolidayEvents($holidays, $startDate, $endDate)
    {
        $events = [];
        foreach ($holidays as $holidayDate => $holidayName) {
            if ($holidayDate >= $startDate && $holidayDate <= $endDate) {
                $events[] = [
                    'title' => $holidayName,
                    'start' => $holidayDate,
                    'backgroundColor' => '#808000',
                ];
            }
        }

        return $events;
    }



}
