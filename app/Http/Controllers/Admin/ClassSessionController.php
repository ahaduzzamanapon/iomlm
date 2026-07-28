<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Timeline;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher', 'mergedGroups.batch']);

        if ($status) {
            $query->where('status', $status);
        }

        $classes = $query->latest()->get();
        return view('admin.classes.index', compact('classes', 'status'));
    }

    public function show(ClassSession $class)
    {
        $class->load(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher', 'attendances.student', 'mergedGroups.batch']);
        return view('admin.classes.show', compact('class'));
    }
}
