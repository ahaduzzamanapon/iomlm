<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Exam;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $stats = [
            'active_students'   => Student::where('status', 'ACTIVE')->count(),
            'pending_leads'     => Student::where('status', 'PENDING')->count(),
            'active_teachers'   => Teacher::where('is_active', true)->count(),
            'total_courses'     => Course::where('is_active', true)->count(),
            'active_batches'    => Batch::where('status', 'ACTIVE')->count(),
            'completed_classes' => ClassSession::where('status', 'COMPLETED')->count(),
            'total_exams'       => Exam::count(),
        ];

        return view('admin.reports.index', compact('stats'));
    }
}
