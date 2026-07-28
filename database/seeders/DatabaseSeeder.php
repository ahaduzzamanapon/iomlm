<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ═════════════════════════════════════════════════════════════════
        // 1. LOGIN USERS (ADMIN, TEACHERS, STUDENTS)
        // ═════════════════════════════════════════════════════════════════
        
        // Admin
        User::create([
            'name'     => 'IOM Central Admin',
            'email'    => 'admin@learningplus.com',
            'password' => $password,
            'role'     => 'admin',
        ]);

        // Teachers (IOM Faculty Members)
        User::create([
            'name'     => 'Dr. Shaikh Ahmadullah',
            'email'    => 'teacher@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        User::create([
            'name'     => 'Dr. Manzur-e-Elahi',
            'email'    => 'ada@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        User::create([
            'name'     => 'Prof. Abu Bakr Muhammad',
            'email'    => 'shannon@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        // Students (IOM Enrolled Students)
        User::create([
            'name'     => 'Abdullah Al Mamun',
            'email'    => 'student@learningplus.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        User::create([
            'name'     => 'Ayesha Siddiqua',
            'email'    => 'sarah@learningplus.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        User::create([
            'name'     => 'Tanvir Hossain',
            'email'    => 'tanvir@gmail.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 2. SYSTEM SETTINGS
        // ═════════════════════════════════════════════════════════════════
        Setting::create(['key' => 'institute_name',         'value' => 'Islamic Online Media (IOM)',       'type' => 'string', 'group' => 'general', 'label' => 'Institute Name']);
        Setting::create(['key' => 'min_attendance_required', 'value' => '0',                                'type' => 'bool',   'group' => 'academic', 'label' => 'Require Minimum Attendance for Exam']);
        Setting::create(['key' => 'min_attendance_percent',  'value' => '75',                               'type' => 'int',    'group' => 'academic', 'label' => 'Minimum Attendance %']);
        Setting::create(['key' => 'final_result_policy',     'value' => 'BEST_ATTEMPT',                     'type' => 'string', 'group' => 'academic', 'label' => 'Multi-attempt Result Policy']);
        Setting::create(['key' => 'due_enforcement_level',   'value' => 'NONE',                             'type' => 'string', 'group' => 'accounts', 'label' => 'Fee Due Enforcement Level']);
    }
}
