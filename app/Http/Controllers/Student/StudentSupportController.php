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
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('phone', $student?->phone)
            ->latest()
            ->get();

        return view('student.support.index', compact('departments', 'user', 'student', 'myTickets'));
    }
}
