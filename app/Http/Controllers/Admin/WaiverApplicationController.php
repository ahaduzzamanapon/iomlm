<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseFeePackage;
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
        $waiverApplication->load(['division', 'reviewer', 'course', 'approvedPackage']);

        // Load course fee packages so the approval modal can show them
        $coursePackages = collect();
        if ($waiverApplication->course_id) {
            $coursePackages = CourseFeePackage::with('items.feeHead')
                ->where('course_id', $waiverApplication->course_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        }

        return view('admin.waiver_applications.show', compact('waiverApplication', 'coursePackages'));
    }

    /**
     * Smart approval — handles 3 waiver types:
     *  - ADMISSION_FEE  → admin sets the actual admission fee student will pay
     *  - TUITION_FEE    → admin selects an approved fee package for the student
     *  - BOTH           → both of the above
     */
    public function approve(Request $request, WaiverApplication $waiverApplication)
    {
        $applyFor = $waiverApplication->apply_for ?? 'BOTH';

        // Build validation rules based on waiver type
        $rules = [
            'reviewer_notes' => 'nullable|string|max:1000',
        ];

        if (in_array($applyFor, ['ADMISSION_FEE', 'BOTH'])) {
            $rules['approved_admission_fee'] = 'required|numeric|min:0';
        }

        if (in_array($applyFor, ['TUITION_FEE', 'BOTH'])) {
            $rules['approved_package_id'] = 'required|exists:course_fee_packages,id';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'status'       => 'APPROVED',
            'reviewer_notes' => $validated['reviewer_notes'] ?? null,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            // Keep legacy percent field at 0 (not used in new flow)
            'approved_discount_percent' => 0,
            'approved_discount_value'   => 0,
            'discount_type'             => 'FIXED',
        ];

        if (in_array($applyFor, ['ADMISSION_FEE', 'BOTH'])) {
            $updateData['approved_admission_fee'] = (float) $validated['approved_admission_fee'];
        }

        if (in_array($applyFor, ['TUITION_FEE', 'BOTH'])) {
            $updateData['approved_package_id'] = $validated['approved_package_id'];
        }

        $waiverApplication->update($updateData);

        // Build success message
        $parts = [];
        if (isset($updateData['approved_admission_fee'])) {
            $parts[] = "Admission Fee: ৳" . number_format($updateData['approved_admission_fee'], 0);
        }
        if (isset($updateData['approved_package_id'])) {
            $pkg = CourseFeePackage::find($updateData['approved_package_id']);
            $parts[] = "Package: " . ($pkg?->name ?? 'Selected');
        }

        $summary = implode(' | ', $parts);

        return back()->with('success', "Waiver Application {$waiverApplication->application_no} APPROVED! — {$summary}");
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
