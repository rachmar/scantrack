<?php

namespace App\Console\Commands;

use App\Jobs\SendConsecutiveAbsentNotification;
use App\Jobs\SendMoreThanSixAbsentNotification;
use App\Models\AbsenceRecord;
use App\Models\Holiday;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StudentAbsentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brokenshire:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks student attendance records and logs absence notifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $students = Student::get();

        foreach ($students as $student) {

            if (!$student) {
                $this->error('Student not found.');
                return;
            }
    
            $studentLevel = $student->isBasicEducation() ? 'basic' : 'college';
            $studentActiveSemester = Semester::where('active', true)
                ->where('level', $studentLevel)
                ->first();
    
            if (!$studentActiveSemester) {
                $this->error('No active semester found for the student.');
                return;
            }
    
            $studentAttendance = $this->studentAttendanceSheet($student, $studentActiveSemester);

            $studentAbsence = $student->isBasicEducation()
                ? $this->hasConsecutiveAbsents($studentAttendance)
                : $this->hasSixOrMoreAbsences($studentAttendance);
            
            $absense = $this->storeAbsenceRecords($student, $studentActiveSemester, $studentAbsence);
    
            $this->info('Student ID: '.$student->id. ' - '.$absense);
        }

    }

    /**
     * Stores absence records for a student.
     *
     * @param Student $student
     * @param Semester $studentActiveSemester
     * @param array $studentAbsence
     * @return void
     */
    private function storeAbsenceRecords(Student $student, Semester $studentActiveSemester, array $studentAbsence)
    {   
        if (!$studentAbsence['status']) {
            return 'No absences recorded.';
        }

        foreach ($studentAbsence['dates'] as $date) {
            AbsenceRecord::updateOrCreate([
                'student_id' => $student->id,
                'semester_id' => $studentActiveSemester->id, 
                'date' => $date,
            ]);
        }

        // $studentAbsence = $student->isBasicEducation()
        //     ? SendConsecutiveAbsentNotification::dispatch($student)
        //     : SendMoreThanSixAbsentNotification::dispatch($student);

        return 'Absence records updated successfully.';
    }

    /**
     * Checks if a student has three or more consecutive absences.
     *
     * @param array $attendanceArray
     * @return array
     */
    private function hasConsecutiveAbsents(array $attendanceArray): array
    {
        $absentStreak = 0;
        $absentDates = [];
        $consecutiveAbsences = [];

        foreach ($attendanceArray as $date => $status) {
            if ($status === 'ABSENT') {
                $absentStreak++;
                $absentDates[] = $date;

                if ($absentStreak >= 3) {
                    $consecutiveAbsences = array_merge($consecutiveAbsences, $absentDates);
                }
            } else {
                $absentStreak = 0;
                $absentDates = [];
            }
        }

        // Remove duplicates and re-index the array
        $consecutiveAbsences = array_values(array_unique($consecutiveAbsences));

        return !empty($consecutiveAbsences) 
            ? ['status' => true, 'dates' => $consecutiveAbsences] 
            : ['status' => false];
    }

    /**
     * Checks if a student has eight or more absences.
     *
     * @param array $attendanceArray
     * @return array
     */
    private function hasSixOrMoreAbsences(array $attendanceArray): array
    {
        $absentDates = array_keys(array_filter($attendanceArray, fn($status) => $status === 'ABSENT'));
        
        return count($absentDates) >= 6 ? ['status' => true, 'dates' => $absentDates] : ['status' => false];
    }

    /**
     * Retrieves the student's attendance records for the semester.
     *
     * @param Student $student
     * @param Semester $studentActiveSemester
     * @return array
     */
    private function studentAttendanceSheet(Student $student, Semester $studentActiveSemester): array
    {
        $startDate = Carbon::parse($studentActiveSemester->start_date);
        $endDate = Carbon::parse($studentActiveSemester->end_date);
        $scheduleDays = $student->currentSchedule();
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->pluck('name', 'date')->toArray();
        
        $attendanceQuery = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        $studentAttendance = $this->studentPresentAttendance($attendanceQuery, $holidays);

        $studentAbsenceRecord = $student->absenceRecord();

        $dateStatuses = [];

        $dayMap = ['M' => 1, 'T' => 2, 'W' => 3, 'TH' => 4, 'F' => 5, 'S' => 6];
        
        while ($startDate->lte($endDate)) {
            $dateString = $startDate->toDateString();
            $dayNumber = $startDate->dayOfWeek ?: 7;

            if (isset($holidays[$dateString])) {
                $dateStatuses[$dateString] = 'EVENT';
            }elseif (isset($studentAbsenceRecord[$dateString])) {
                $dateStatuses[$dateString] = 'CLEAR';
            } elseif (in_array(array_search($dayNumber, $dayMap), $scheduleDays)) {
                $dateStatuses[$dateString] = isset($studentAttendance[$dateString]) ? 'PRESENT' : 'ABSENT';
            }
            
            $startDate->addDay();
        }
        
        return $dateStatuses;
    }

    /**
     * Retrieves student attendance records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $attendanceQuery
     * @param array $holidays
     * @return array
     */
    private function studentPresentAttendance($attendanceQuery, array $holidays): array
    {
        return $attendanceQuery->clone()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray() ?? [];
    }
}
