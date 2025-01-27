<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Student;
use App\Models\Visitor;
use Faker\Factory as Faker;

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

        $faker = Faker::create();

        // Create 40 Students
        for ($i = 1; $i <= 40; $i++) {
            Student::create([
                'card_id' => 'CARD' . $faker->unique()->numberBetween(100000, 999999),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'image' => 'student1.jpg',
            ]);
        }

        // Create 20 Visitors
        for ($i = 1; $i <= 20; $i++) {
            Visitor::create([
                'card_id' => 'CARD' . $faker->unique()->numberBetween(100000, 999999),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'purpose' => $faker->randomElement(['Get School Diploma', 'Admission Inquiry', 'Visit Friend', 'Attend Seminar']),
            ]);
        }

    }
}
