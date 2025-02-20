<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Directory;
use App\Models\Holiday;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function courseReportIndex(Request $request)
    {   
        $semesters = Semester::get();

        $departments = Department::get();

        $startDate = null;
        $endDate = null;

        $semester = Semester::where('id', $request->semester)->first();

        if ($semester) {
            $startDate = $semester->start_date;
            $endDate = $semester->end_date;
        } 

        $studentsPerDepartment = Department::withCount('students')->get()->mapWithKeys(function ($department) {
            return [$department->slug => $department->students_count];
        });

        $studentsPerCourse = Department::with('courses')->get()->mapWithKeys(function ($department) {
            return $department->courses->mapWithKeys(function ($course) {
                return [$course->slug => $course->students()->count()];
            });
        });

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
            ->when($request->department, function ($query, $department) {
                return $query->where("departments.id", $department);
            })
            ->when($request->course, function ($query, $course) {
                return $query->where("courses.id", $course);
            })
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

        // Calculate total school days excluding Sundays
        $totalSchoolDaysPerDepartment = collect();
        foreach ($attendanceSummedByDepartment->keys() as $department) {
            $totalSchoolDays = Carbon::parse($startDate)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isSunday();
            }, Carbon::parse($endDate)) + 1;
            $totalSchoolDaysPerDepartment[$department] = $totalSchoolDays;
        }

        // Absenteeism Rate per department
        $absenteeismReport = $attendanceSummedByDepartment->map(function ($attendance, $department) use ($totalSchoolDaysPerDepartment, $studentsPerDepartment) {
            // Calculate Total Possible Attendance Days
            $totalPossibleAttendanceDays = $studentsPerDepartment[$department] * $totalSchoolDaysPerDepartment[$department];
            // Calculate Total Actual Attendance Days (sum of distinct attendance days)
            $totalActualAttendanceDays = $attendance; // Assuming $attendance contains the sum of distinct attendance days
            // Calculate absenteeism rate
            $absenteeismRate = (($totalPossibleAttendanceDays - $totalActualAttendanceDays) / $totalPossibleAttendanceDays) * 100;
            return [
                'department' => $department,
                'absenteeism_rate' => round($absenteeismRate, 2),
            ];
        });

        // Calculate total school days excluding Sundays
        $totalSchoolDaysPerCourse = collect();
        foreach ($attendanceSummedByCourse->keys() as $course) {
            $totalSchoolDays = Carbon::parse($startDate)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isSunday();
            }, Carbon::parse($endDate)) + 1;
            $totalSchoolDaysPerCourse[$course] = $totalSchoolDays;
        }

         // Absenteeism Rate per department
        $absenteeismRateCourse = $attendanceSummedByCourse->map(function ($attendance, $course) use ($totalSchoolDaysPerCourse, $studentsPerCourse) {
            // Calculate Total Possible Attendance Days
            $totalPossibleAttendanceDays = $studentsPerCourse[$course] * $totalSchoolDaysPerCourse[$course];
            // Calculate Total Actual Attendance Days (sum of distinct attendance days)
            $totalActualAttendanceDays = $attendance; // Assuming $attendance contains the sum of distinct attendance days
            // Calculate absenteeism rate
            $absenteeismRate = (($totalPossibleAttendanceDays - $totalActualAttendanceDays) / $totalPossibleAttendanceDays) * 100;
            return [
                'course' => $course,
                'absenteeism_rate' => round($absenteeismRate, 2),
            ];
        });
        

        // Prepare chart data
        $chartData = [
            'courseLabels' => $attendanceSummedByCourse->keys()->toArray(),
            'courseAttendance' => $attendanceSummedByCourse->values()->toArray(),
            'departmentLabels' => $attendanceSummedByDepartment->keys()->toArray(),
            'departmentAttendance' => $attendanceSummedByDepartment->values()->toArray(),
        ];

        return view('admin.reports.courses.index', compact(
            'attendanceSummedByDepartment',
            'attendanceSummedByCourse',
            'absenteeismReport', 
            'chartData',
            'absenteeismRateCourse', 
            'departments',
            'semesters',
            'semester'
        ));
    }

    public function studentReportIndex(Request $request)
    {
        $query = Student::with(['course.department']); // Eager load course and department relationships
    
        // Check if there are any filters in the request
        if ($request->filled('name') || $request->filled('department') || $request->filled('course')) {
            // Apply filters if 'name' parameter is provided
            if ($request->filled('name')) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%'])
                    ->orWhereRaw("card_id LIKE ?", ['%' . $request->name . '%']);
            }
    
            // Apply filters if 'department' parameter is provided
            if ($request->filled('department')) {
                $query->whereHas('course', function ($query) use ($request) {
                    $query->where('department_id', $request->department);
                });
            }
    
            // Apply filters if 'course' parameter is provided
            if ($request->filled('course')) {
                $query->where('course_id', $request->course);
            }
    
            // Fetch filtered students
            $students = $query->get();
        } else {
            // Return an empty collection if no filters are provided
            $students = collect();
        }
    
        // Get all departments with their associated courses (not filtered by request here)
        $departments = Department::with('courses')->get();
    
        return view('admin.reports.students.index', compact('students', 'departments'));
    }

    public function studentReportShow(Request $request, $id)
    {
        $student = Student::with('course.department')->where('id', $id)->first();

        $semesterQuery = Semester::query();

        if ($request->semester) {
            $activeSemesterQuery = Semester::where('id', $request->semester);
        }else{
            $activeSemesterQuery = Semester::where('active', true);
        }
        
        $level = $student->isBasicEducation() ? 'basic' : 'college';
        
        $semesters = $semesterQuery->where('level', $level)->get();

        
        $activeSemester = $activeSemesterQuery->where('level', $level)->first();


        $startDate = $activeSemester->start_date ?? null;
        $endDate = $activeSemester->end_date ?? null ;

        // Convert student's schedule into an array
        $scheduleDays = $student->currentSchedule();

        // Fetch holidays within the date range
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('name', 'date')
            ->toArray();

        // Get all valid scheduled dates for the student within the date range
        $scheduledDates = $this->getScheduledDates($startDate, $endDate, $scheduleDays, $holidays);

        // Calculate number of school days based on the schedule
        $numOfSchoolDays = count($scheduledDates);

        // Define the common query logic for attendance
        $attendanceQuery = DB::table("student_attendances")
            ->where("student_attendances.student_id", $student->id ?? 0)
            ->whereBetween("student_attendances.created_at", [$startDate, $endDate]);

        // Get attendance counts per day (allowing multiple entries)
        $studentAttendance = $this->getAttendanceCounts($attendanceQuery, $holidays);

        // Get all attendance records per day (listing entries)
        $studentAttendanceTable = $this->getAttendanceRecords($attendanceQuery, $holidays);

        // Calculate total attendance and absences
        $numOfStudentAttendance = count($studentAttendance);
        $numOfAbsences = $numOfSchoolDays - $numOfStudentAttendance;

        if ($numOfSchoolDays > 0) {
            $absenteeismRate = round((($numOfAbsences / $numOfSchoolDays) * 100), 2);
        } else {
            $absenteeismRate = 0; // or handle the case as needed
        }
        
        // Check if the student is present today
        $isPresentToday = isset($studentAttendance[Carbon::today()->toDateString()]);

        // Prepare attendance data for charts
        $dailyLabels = array_keys($studentAttendance);
        $dailyValues = array_values($studentAttendance);

        $dailyAttendanceEvents = $this->prepareAttendanceEvents($studentAttendance, '#008000');
        $absenceEvents = $this->prepareAbsenceEvents($scheduledDates, array_keys($studentAttendance), '#800000');
        $holidayEvents = $this->prepareHolidayEvents($holidays, $startDate, $endDate);

        return view(
            "admin.reports.students.show",
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
                "absenteeismRate",
                "semesters",
                "activeSemester"
            )
        );
    }

    public function visitorReportIndex(Request $request)
    {   
        // Get today's date for both start and end date, default to whole month if not provided
        $startDate = $request->get("start_date", Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->endOfMonth()->toDateString());

        $visitorPerDirectories = Directory::withCount([
            'visitors' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        ])->get()->mapWithKeys(function ($directory) {
            return [$directory->name => $directory->visitors_count];
        });

        // Prepare chart data
        $chartData = [
            'visitorPerDirectoryLabels' => $visitorPerDirectories->keys()->toArray(),
            'visitorPerDirectoryValues' => $visitorPerDirectories->values()->toArray(),
        ];

        return view(
            "admin.reports.visitors.index",
            compact(
                "startDate",
                "endDate",
                "visitorPerDirectories",
                "chartData"
            )
        );
    }

    public function visitorReportShow(Request $request, $slug)
    {

        $directory = Directory::where('slug', $slug)->first();

        // Get today's date for both start and end date, default to whole month if not provided
        $startDate = $request->get("start_date", Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->endOfMonth()->toDateString());

        $visitors = Visitor::whereBetween('created_at', [$startDate, $endDate])
        ->whereHas('directory', function ($query) use ($directory) {
            $query->where('slug', $directory->slug);
        })
        ->with('directory')
        ->get();


    
        return view(
            "admin.reports.visitors.show",
            compact(
                "startDate",
                "endDate",
                "directory",
                "visitors",
            )
        );
    }

    public function getCourses(Request $request)
    {
        $courses = Course::where('department_id', $request->department_id)->get();
        return response()->json($courses);
    }

    public function getSemesters(Request $request)
    {
        $course = Course::where('id', $request->course_id)->first();

        $semesters = Semester::getSemesterByCourse($course);

        return response()->json($semesters);
    }

    // Extracted function to get the scheduled dates
    private function getScheduledDates($startDate, $endDate, $scheduleDays, $holidays)
    {
         // Map days of the week to their respective codes
         $dayMap = [
            'M'  => 1,  // Monday
            'T'  => 2,  // Tuesday
            'W'  => 3,  // Wednesday
            'TH' => 4,  // Thursday
            'F'  => 5,  // Friday
            'S'  => 6,  // Saturday
        ];

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
