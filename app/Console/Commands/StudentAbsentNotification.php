<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StudentAbsentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brokenshire:studentabsentnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get today's date for both start and end date, default to whole month if not provided
        $startDate = $request->get("start_date", Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->get("end_date", Carbon::today()->endOfMonth()->toDateString());

        $student = Student::where('id', 18)->first();

        // Convert student's schedule into an array
        $scheduleDays = $student->schedule ?? [];

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

        // Initialize the array to store the status for each date
        $dateStatuses = [];

        // Loop through the date range and generate the status for each date
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $dayMap = [
            'M'  => 1,  // Monday
            'T'  => 2,  // Tuesday
            'W'  => 3,  // Wednesday
            'TH' => 4,  // Thursday
            'F'  => 5,  // Friday
            'S'  => 6,  // Saturday
        ];

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->toDateString();
            $dayNumber = $currentDate->dayOfWeek;
            $dayNumber = ($dayNumber == 0) ? 7 : $dayNumber;
            // Check if it's a holiday
            if (isset($holidays[$dateString])) {
                $dateStatuses[$dateString] = "EVENT";
            }
            // Check if it's a scheduled class day
            elseif (in_array(array_search($dayNumber, $dayMap), $scheduleDays)) {
                // Check if the student attended
                if (isset($studentAttendance[$dateString]) && $studentAttendance[$dateString] > 0) {
                    $dateStatuses[$dateString] = "PRESENT";
                } else {
                    $dateStatuses[$dateString] = "ABSENT";
                }
            }
            // Move to the next day
            $currentDate->addDay();
        }

        return $dateStatuses;
    }
}
