<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\Directory;
use App\Models\Holiday;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Visitor;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            "name" => "Admin",
            "email" => "admin@admin.com",
            "password" => bcrypt("adminadmin"),
        ]);

        $role = Role::create([
            "slug" => "admin",
            "name" => "Adminstrator",
        ]);

        $user->roles()->sync($role->id);

        $courses = [
            "ASBME" => [
                "Bachelor of Science in Psychology (BSPsych)",
                "Bachelor of Science in Business Administration (BSBA)",
                "Bachelor of Science in Information Technology (BSIT)",
                "Bachelor of Science in Secondary Education (BSED)",
                "Bachelor of Science in Elementary Education (BEED)",
                "Bachelor of Science in Hospitality Management (BSHM)",
            ],
            "ALLIED HEALTH" => [
                "Bachelor of Science in Pharmacy (BSPh)",
                "Bachelor of Science in Medical Technology (BSMT)",
            ],
            "BASIC EDUCATION" => [
                "Junior High (JuniorHigh)",
                "Senior High (SeniorHigh)",
            ],
            "NURSING" => [
                "Bachelor of Science in Nursing (BSN)",
            ],
            "MEDICINE" => [
                "Bachelor of Science in Medicine (BSM)",
            ]
        ];
        
        // Seed departments and courses
        foreach ($courses as $departmentName => $departmentCourses) {
            // Create department
            $department = Department::create([
                'name' => $departmentName,
                'slug' => $departmentName,
            ]);
        
            foreach ($departmentCourses as $course) {
                // Extract the abbreviation (e.g., BSN, BSIT) from the course name
                preg_match("/\((.*?)\)/", $course, $matches);
                $abbreviation = $matches[1];
        
                // Create course associated with the department
                Course::create([
                    'slug' => strtoupper(Str::slug($abbreviation)),
                    'name' => $course,
                    'department_id' => $department->id, // Associate course with the department
                ]);
            }
        }

        Semester::create([
            'name' => 'AY 1st Semester (COLLEGE) 2025',
            'start_date' => Carbon::create(2025, 1, 01),
            'end_date' => Carbon::create(2025, 2, 28 ),
        ]);

        Semester::create([
            'name' => 'AY Whole Year (CDC, HS, SHS) 2025',
            'start_date' => Carbon::create(2025, 1, 01),
            'end_date' => Carbon::create(2025, 2, 28 ),
        ]);

        $directories = [
            "ADMISSION",
            "REGISTRAR",
            "CASHIER",
            "ACCOUNTING",
            "NURSING",
            "MEDICINE",
            "ASBME",
            "BASIC EDUCATION",
            "ALLIED HEALTH"
        ];

        foreach ($directories as $directory) {
            Directory::create([
                'name' => $directory,
                'slug' => $directory,
            ]);
        }

        $directoryIDs = Directory::pluck("id")->toArray();

        $faker = Faker::create();

        for ($i = 1; $i <= 100; $i++) {
            Visitor::create([
                'card_id' => strtoupper('VIS'. bin2hex(random_bytes(5))),
                'name' => $faker->firstName.' '.$faker->lastName,
                "phone" => $faker->phoneNumber,
                "directory_id" => $faker->randomElement($directoryIDs), // Assign random course ID
                'purpose' => $faker->sentence,
            ]);
        }
        
        $events = [
            ["name" => "Event 1", "date" => Carbon::create(2025, 1, 14)],
            ["name" => "Event 2", "date" => Carbon::create(2025, 1, 15)],
            ["name" => "Event 3", "date" => Carbon::create(2025, 2, 14)],
        ];

        foreach ($events as $event) {
            Holiday::create([
                "name" => $event["name"],
                "date" => $event["date"],
            ]);
        }

        $faker = Faker::create();

        // Fetch all course IDs
        $courseIds = Course::pluck("id")->toArray();

        // Define available days (excluding Sunday)
        $dayMap = [
            "M" => 1, // Monday
            "T" => 2, // Tuesday
            "W" => 3, // Wednesday
            "TH" => 4, // Thursday
            "F" => 5, // Friday
            "S" => 6, // Saturday
        ];

        $dayKeys = array_keys($dayMap); // Get only the keys like ['M', 'T', 'W', 'TH', 'F', 'S']

        // Create 500 Students
        for ($i = 1; $i <= 20; $i++) {
            $scheduleCount = rand(3, 5); // Ensure at least 3 and at most 5 days
            $schedule = $faker->randomElements($dayKeys, $scheduleCount); // Select random days

            // Sort schedule based on day order defined in $dayMap
            usort($schedule, function ($a, $b) use ($dayMap) {
                return $dayMap[$a] - $dayMap[$b];
            });

            Student::create([
                "card_id" =>
                    "ST" . $faker->unique()->numberBetween(100000, 999999),
                "course_id" => $faker->randomElement($courseIds), // Assign random course ID
                "first_name" => $faker->firstName,
                "last_name" => $faker->lastName,
                "email" => $faker->unique()->safeEmail,
                "phone" => $faker->phoneNumber,
                "image" => "blank.jpg",
                "schedule" => $schedule, // Store sorted schedule as a comma-separated string
            ]);
        }

        // Fetch all student IDs with their schedules
        $students = Student::select("id", "schedule")
            ->get()
            ->keyBy("id")
            ->toArray();

        // Define the date range
        $startDate = Carbon::create(2025, 01, 1);
        $endDate = Carbon::create(2025, 2, 28);

        // Day map for checking attendance days
        $dayMap = [
            1 => "M", // Monday
            2 => "T", // Tuesday
            3 => "W", // Wednesday
            4 => "TH", // Thursday
            5 => "F", // Friday
            6 => "S", // Saturday
        ];

        // Track absences per student per week
        $weeklyAbsences = [];
       
        while ($startDate->lte($endDate)) {
            // Check if the current date is in the events array
            $isEventDay = collect($events)->contains(function ($event) use ($startDate) {
                return $event['date']->isSameDay($startDate);
            });
        
            // Skip the current date if it's an event day
            if ($isEventDay) {
                $startDate->addDay(); // Move to the next day
                continue;
            }
        
            if (!$startDate->isSunday()) {
                $dayCode = $dayMap[$startDate->dayOfWeek] ?? null; // Get day code (M, T, W, etc.)
        
                if ($dayCode) {
                    $currentWeek = $startDate
                        ->copy()
                        ->startOfWeek()
                        ->toDateString(); // Identify the week
        
                    foreach ($students as $studentId => $student) {
                        $studentSchedule = $student["schedule"]; // Convert schedule to array
        
                        // If the student is scheduled for this day
                        if (in_array($dayCode, $studentSchedule)) {
                            // Initialize weekly absence tracking
                            if (
                                !isset(
                                    $weeklyAbsences[$studentId][$currentWeek]
                                )
                            ) {
                                $weeklyAbsences[$studentId][$currentWeek] = 0;
                            }
        
                            // Decide if the student should be absent (only if they haven't been absent this week)
                            $isAbsent =
                                $weeklyAbsences[$studentId][$currentWeek] == 0 && rand(1, 10) > 8; // 20% chance
        
                            if ($isAbsent) {
                                $weeklyAbsences[$studentId][$currentWeek]++; // Mark as absent for this week
                            } else {
                                // Generate multiple IN and OUT entries per student
                                $numEntries = rand(2, 4);
                                $inTimeBase = $startDate->copy()->setHour(8); // Base IN time
        
                                for ($i = 0; $i < $numEntries; $i++) {
                                    // Ensure IN times are spaced out correctly
                                    $inTime = $inTimeBase
                                        ->copy()
                                        ->addMinutes(rand(0, 30)); // Randomize IN time slightly
        
                                    // Ensure OUT time is always at least 30 minutes after IN time
                                    $outTime = $inTime
                                        ->copy()
                                        ->addMinutes(rand(30, 90)); // OUT time after IN time
        
                                    // Update the inTimeBase for the next iteration, making sure the times don't overlap
                                    $inTimeBase = $outTime->copy(); // The next IN time will be after the current OUT time
        
                                    // Create the "IN" attendance record
                                    StudentAttendance::create([
                                        "student_id" => $studentId,
                                        "status" => "IN",
                                        "created_at" => $inTime,
                                        "updated_at" => $inTime,
                                    ]);
        
                                    // Create the "OUT" attendance record
                                    StudentAttendance::create([
                                        "student_id" => $studentId,
                                        "status" => "OUT",
                                        "created_at" => $outTime,
                                        "updated_at" => $outTime,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            $startDate->addDay(); // Move to the next day
        }

    }
}
