<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaiverApplication;
use Illuminate\Http\Request;

class WaiverApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '');
        $search = $request->query('search', '');

        $query = WaiverApplication::with(['division', 'reviewer'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20);
        $pendingCount  = WaiverApplication::where('status', 'PENDING')->count();
        $approvedCount = WaiverApplication::where('status', 'APPROVED')->count();
        $rejectedCount = WaiverApplication::where('status', 'REJECTED')->count();

        return view('admin.waiver_applications.index', compact(
            'applications', 'pendingCount', 'approvedCount', 'rejectedCount', 'status', 'search'
        ));
    }

    public function show(WaiverApplication $waiverApplication)
    {
        $waiverApplication->load(['division', 'reviewer']);
        return view('admin.waiver_applications.show', compact('waiverApplication'));
    }

    public function approve(Request $request, WaiverApplication $waiverApplication)
    {
        $validated = $request->validate([
            'discount_type'           => 'required|in:PERCENTAGE,FIXED',
            'approved_discount_value' => 'required|numeric|min:0',
            'reviewer_notes'            => 'nullable|string',
        ]);

        $discType = $validated['discount_type'];
        $discVal  = $validated['approved_discount_value'];

        $waiverApplication->update([
            'status'                    => 'APPROVED',
            'discount_type'             => $discType,
            'approved_discount_value'   => $discVal,
            'approved_discount_percent' => $discType === 'PERCENTAGE' ? $discVal : 0,
            'reviewer_notes'            => $validated['reviewer_notes'] ?? null,
            'reviewed_by'               => auth()->id(),
            'reviewed_at'               => now(),
        ]);

        $displayText = $discType === 'PERCENTAGE' ? "{$discVal}%" : "৳{$discVal} Fixed";

        return back()->with('success', "Waiver Application {$waiverApplication->application_no} APPROVED with {$displayText} waiver!");
    }

    public function reject(Request $request, WaiverApplication $waiverApplication)
    {
        $validated = $request->validate([
            'reviewer_notes' => 'required|string|max:500',
        ]);

        $waiverApplication->update([
            'status'         => 'REJECTED',
            'reviewer_notes' => $validated['reviewer_notes'],
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        return back()->with('success', "Waiver Application {$waiverApplication->application_no} REJECTED.");
    }
}
