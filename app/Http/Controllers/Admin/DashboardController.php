<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_students'     => $this->count(\App\Models\Student::class, ['status' => 'ACTIVE']),
            'total_teachers'     => $this->count(\App\Models\Teacher::class, ['is_active' => true]),
            'total_courses'      => $this->count(\App\Models\Course::class, ['is_active' => true]),
            'active_batches'     => $this->count(\App\Models\Batch::class, ['status' => 'ACTIVE']),
            'pending_admissions' => $this->count(\App\Models\AdmissionForm::class, ['status' => 'PENDING']),
            'today_classes'      => $this->count(\App\Models\ClassSession::class, ['status' => 'SCHEDULED']),
        ];

        $pendingAdmissions = \App\Models\AdmissionForm::with(['student', 'interestedCourse'])
            ->where('status', 'PENDING')
            ->latest()
            ->take(5)
            ->get();

        $todayClasses = \App\Models\ClassSession::with(['timeline.subject', 'timeline.module', 'teacher'])
            ->where('status', 'SCHEDULED')
            ->take(6)
            ->get();

        $activeBatches = \App\Models\Batch::with('course')
            ->where('status', 'ACTIVE')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'pendingAdmissions', 'todayClasses', 'activeBatches'
        ));
    }

    private function count(string $model, array $where = []): int
    {
        try {
            return $model::where($where)->count();
        } catch (\Exception) {
            return 0;
        }
    }
}
