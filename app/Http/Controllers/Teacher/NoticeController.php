<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\RoutineEntry;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $batchIds = collect();
        if ($teacher) {
            $batchIds = SubjectTeacherAssignment::where('teacher_id', $teacher->id)->pluck('batch_id')
                ->merge(RoutineEntry::where('teacher_id', $teacher->id)->pluck('batch_id'))
                ->merge(ClassSession::where('teacher_id', $teacher->id)->pluck('batch_id'))
                ->filter()->unique();
        }

        $notices = Notice::with(['batch', 'semester', 'creator'])
            ->where('is_published', true)
            ->whereIn('target_audience', ['ALL', 'TEACHERS'])
            ->where(function($q) use ($batchIds) {
                $q->whereNull('batch_id');
                if ($batchIds->isNotEmpty()) {
                    $q->orWhereIn('batch_id', $batchIds);
                }
            })
            ->latest()
            ->paginate(15);

        return view('teacher.notices.index', compact('notices'));
    }
}
