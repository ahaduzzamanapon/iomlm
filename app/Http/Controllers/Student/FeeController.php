<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $student  = Student::where('email', auth()->user()->email)->first();
        
        $invoices = Invoice::with('enrollment.course')
            ->where('student_id', $student?->id)
            ->latest()
            ->get();

        $payments = Payment::with('invoice')
            ->where('student_id', $student?->id)
            ->latest('paid_at')
            ->get();

        $totalDue  = $invoices->where('status', '!=', 'CANCELLED')->sum('due_amount');
        $totalPaid = $payments->sum('amount');

        return view('student.fees.index', compact('invoices', 'payments', 'totalDue', 'totalPaid'));
    }
}
