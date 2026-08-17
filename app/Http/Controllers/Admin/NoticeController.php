<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Batch;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with(['batch', 'creator'])->latest()->paginate(20);
        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();
        return view('admin.notices.index', compact('notices', 'batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:250',
            'content'         => 'required|string',
            'target_audience' => 'required|in:ALL,STUDENTS,TEACHERS',
            'batch_id'        => 'nullable|exists:batches,id',
            'priority'        => 'required|in:NORMAL,IMPORTANT,URGENT',
        ]);

        Notice::create([
            'title'           => $validated['title'],
            'content'         => $validated['content'],
            'target_audience' => $validated['target_audience'],
            'batch_id'        => $validated['batch_id'] ?? null,
            'priority'        => $validated['priority'],
            'created_by'      => auth()->id(),
            'is_published'    => true,
        ]);

        return back()->with('success', 'Notice published successfully!');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return back()->with('success', 'Notice deleted.');
    }
}
