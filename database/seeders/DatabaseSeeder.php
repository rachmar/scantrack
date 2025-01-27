<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Student;
use Hash;
use Spatie\Permission\Traits\HasRoles;
use DB;
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

        Student::create([
            'card_id' => 'CARD123456',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '1234567890',
            'course' => 'Computer Science',
            'address' => '123 Main St, Anytown, USA',
            'image' => 'student1.jpg',
        ]);

        Student::create([
            'card_id' => 'CARD654321',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'janesmith@example.com',
            'phone' => '9876543210',
            'course' => 'Information Technology',
            'address' => '456 Elm St, Othercity, USA',
            'image' => 'student2.jpg',
        ]);
    }
}
