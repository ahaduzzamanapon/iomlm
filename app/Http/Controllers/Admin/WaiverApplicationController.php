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
            $searchAlt = str_starts_with(strtoupper($search), 'PF-')
                ? str_ireplace('PF-', 'POOR-', $search)
                : (str_starts_with(strtoupper($search), 'POOR-') ? str_ireplace('POOR-', 'PF-', $search) : $search);

            $query->where(function ($q) use ($search, $searchAlt) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$search}%")
                  ->orWhere('application_no', 'like', "%{$searchAlt}%")
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
        if ($waiverApplication->course_id) {
            $coursePackages = CourseFeePackage::with(['items.feeHead', 'course'])
                ->where('course_id', $waiverApplication->course_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        } else {
            $coursePackages = CourseFeePackage::with(['items.feeHead', 'course'])
                ->where('is_active', true)
                ->orderBy('course_id')
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

        // ── DISPATCH WAIVER APPROVAL EMAIL WITH COUPON CODE ───────────
        if (!empty($waiverApplication->email) && filter_var($waiverApplication->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $mailService = app(\App\Services\DynamicMailService::class);
                $subject = "🎉 অভিনন্দন: আপনার পুওর ফান্ড আবেদন অনুমোদিত হয়েছে! কুপন কোড: {$waiverApplication->application_no}";

                $detailsList = [];
                $detailsList[] = "• পুওর ফান্ড / কুপন কোড: {$waiverApplication->application_no}";

                if (isset($updateData['approved_admission_fee'])) {
                    $detailsList[] = "• অনুমোদিত ভর্তি ফি: ৳" . number_format($updateData['approved_admission_fee'], 0);
                }
                if (isset($updateData['approved_package_id'])) {
                    $pkg = CourseFeePackage::find($updateData['approved_package_id']);
                    if ($pkg) {
                        $detailsList[] = "• অনুমোদিত ফি প্যাকেজ: {$pkg->name}";
                    }
                }

                $detailsText = implode("\n", $detailsList);
                $applyUrl = url('/apply?waiver_code=' . urlencode($waiverApplication->application_no));

                $emailBody = "আসসালামু আলাইকুম {$waiverApplication->full_name},\n\n"
                    . "আলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসার প্রশাসন কর্তৃক আপনার পুওর ফান্ড / বিশেষ ছাড়ের আবেদনটি (আবেদন নং: {$waiverApplication->application_no}) সফলভাবে অনুমোদিত হয়েছে।\n\n"
                    . "আপনার অনুমোদিত বিশেষ ছাড় ও কুপন কোডের বিবরণ:\n"
                    . "----------------------------------------\n"
                    . "{$detailsText}\n"
                    . "----------------------------------------\n\n"
                    . "ভর্তি ফরম পূরণ করার সময় আপনার এই কুপন কোডটি ({$waiverApplication->application_no}) ব্যবহার করে আপনি অনুমোদিত নির্ধারিত ফি-তে ভর্তি সম্পন্ন করতে পারবেন।\n\n"
                    . "ভর্তি ফরম পূরণ করতে নিচের লিংকে ক্লিক করুন:\n"
                    . "ভর্তি ফরম লিংক: {$applyUrl}\n\n"
                    . "বিনীত,\n"
                    . "পুওর ফান্ড ও স্কলারশিপ কমিটি,\n"
                    . "ইসলামিক অনলাইন মাদ্রাসা (IOM)";

                $mailService->sendHtmlNotification(
                    $waiverApplication->email,
                    $subject,
                    $emailBody,
                    null,
                    $applyUrl
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Waiver Approval Email Exception: ' . $e->getMessage());
            }
        }

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

        return back()->with('success', "Waiver Application {$waiverApplication->application_no} APPROVED! — {$summary} 📧 Coupon Code email dispatched to applicant.");
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
