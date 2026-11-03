<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TeacherAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_can_login_with_email_employee_id_and_phone()
    {
        $this->withoutExceptionHandling();
        $password = 'secret123';

        $user = User::create([
            'name' => 'Maulana Tariq',
            'email' => 'tariq@iom.edu.bd',
            'password' => Hash::make($password),
            'role' => 'teacher',
            'employee_id' => 'EMP-888',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => 'Maulana Tariq',
            'email' => 'tariq@iom.edu.bd',
            'phone' => '01711998877',
            'employee_id' => 'EMP-888',
            'is_active' => true,
        ]);

        // 1. Login with Email
        $resEmail = $this->post('/login', [
            'email' => 'tariq@iom.edu.bd',
            'password' => $password,
        ]);
        $resEmail->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // 2. Login with Employee ID
        $resEmp = $this->post('/login', [
            'email' => 'EMP-888',
            'password' => $password,
        ]);
        $resEmp->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        // 3. Login with Phone
        $resPhone = $this->post('/login', [
            'email' => '01711998877',
            'password' => $password,
        ]);
        $resPhone->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
