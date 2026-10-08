<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentSupportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user?->student;
        $departments = SupportDepartment::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        $myTickets = SupportTicket::with('department', 'assignedAgent', 'latestMessage')
            ->where(function ($q) use ($user, $student) {
                $q->where('user_id', $user->id);
                if (!empty($user->email)) {
                    $q->orWhere('email', $user->email);
                }
                if (!empty($student?->email)) {
                    $q->orWhere('email', $student->email);
                }
                if (!empty($student?->phone)) {
                    $q->orWhere('phone', $student->phone);
                }
                if (!empty($student?->student_code)) {
                    $cleanCode = str_replace('-', '', $student->student_code);
                    $q->orWhere('student_id', $cleanCode)
                      ->orWhere('student_id', $student->student_code);
                }
            })
            ->latest()
            ->get();

        return view('student.support.index', compact('departments', 'user', 'student', 'myTickets'));
    }
}
