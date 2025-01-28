<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
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
        $user= User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('adminadmin'),
        ]);

        $role = Role::create([
            'slug' => 'admin',
            'name' => 'Adminstrator',
        ]);

        $user->roles()->sync($role->id);

        $courses = [
            'Bachelor of Science in Nursing (BSN)',
            'Bachelor of Science in Information Technology (BSIT)',
            'Bachelor of Science in Psychology (BSPsych)',
            'Bachelor of Science in Accountancy (BSA)',
            'Bachelor of Science in Education (BSEd)',
            'Bachelor of Science in Hospitality Management (BSHM)',
        ];
        
        // Seed courses
        foreach ($courses as $course) {
            // Extract the abbreviation (e.g., BSN, BSIT) from the course name
            preg_match('/\((.*?)\)/', $course, $matches);
            $abbreviation = $matches[1];
        
            Course::create([
                'slug' => Str::slug($abbreviation),
                'name' => $course,
            ]);
        }

        $faker = Faker::create();

        // Fetch all course IDs
        $courseIds = Course::pluck('id')->toArray();

        // Create 100 Students
        for ($i = 1; $i <= 500; $i++) {
            Student::create([
                'card_id' => 'ST' . $faker->unique()->numberBetween(100000, 999999),
                'course_id' => $faker->randomElement($courseIds), // Assign random course ID
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'image' => 'blank.jpg',
            ]);
        }  

         // Fetch all student IDs
         $studentIds = Student::pluck('id')->toArray();

         // Define the date range
         $startDate = Carbon::create(2024, 9, 1);
         $endDate = Carbon::create(2025, 2, 28);
 
         // Generate attendance data
         while ($startDate->lte($endDate)) {
             // Skip Sundays
             if (!$startDate->isSunday()) {
                 // Create attendance for 1-5 random students per day
                 $studentsPerDay = $faker->numberBetween(1, 100);
 
                 for ($i = 0; $i < $studentsPerDay; $i++) {
                     StudentAttendance::create([
                         'student_id' => $faker->randomElement($studentIds),
                         'created_at' => $startDate->toDateTimeString(),
                         'updated_at' => $startDate->toDateTimeString(),
                     ]);
                 }
             }
 
             // Move to the next day
             $startDate->addDay();
         }




        // // Create 20 Visitors
        // for ($i = 1; $i <= 20; $i++) {
        //     Visitor::create([
        //         'card_id' => 'CARD' . $faker->unique()->numberBetween(100000, 999999),
        //         'first_name' => $faker->firstName,
        //         'last_name' => $faker->lastName,
        //         'email' => $faker->unique()->safeEmail,
        //         'phone' => $faker->phoneNumber
        //     ]);
        // }

    }
}
