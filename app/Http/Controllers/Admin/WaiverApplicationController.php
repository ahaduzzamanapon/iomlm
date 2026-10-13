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
        $waiverApplication->refresh();

        // ── DISPATCH WAIVER APPROVAL EMAIL WITH COUPON CODE ───────────
        if (!empty($waiverApplication->email) && filter_var($waiverApplication->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $emailData = $this->buildApprovalEmailData($waiverApplication);
                $mailService = app(\App\Services\DynamicMailService::class);
                $mailService->sendHtmlNotification(
                    $waiverApplication->email,
                    $emailData['subject'],
                    $emailData['body'],
                    null,
                    $emailData['applyUrl']
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

    /**
     * Update applicant contact info (Email & Phone)
     */
    public function updateContact(Request $request, WaiverApplication $waiverApplication)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
        ]);

        $waiverApplication->update([
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // If there's an associated AdmissionForm, sync contact as well
        if ($waiverApplication->admission_form_id) {
            $admissionForm = \App\Models\AdmissionForm::find($waiverApplication->admission_form_id);
            if ($admissionForm) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('admission_forms', 'email')) {
                    $admissionForm->update([
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                    ]);
                }
                if ($admissionForm->student) {
                    $admissionForm->student->update([
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                    ]);
                    if ($admissionForm->student->user) {
                        $admissionForm->student->user->update([
                            'email' => $validated['email'],
                        ]);
                    }
                }
            }
        }

        return back()->with('success', "আবেদনকারী {$waiverApplication->full_name}-এর ইমেইল ও মোবাইল নম্বর সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Resend Approval & Coupon Code Email
     */
    public function resendEmail(WaiverApplication $waiverApplication)
    {
        if ($waiverApplication->status !== 'APPROVED') {
            return back()->with('error', 'শুধুমাত্র অনুমোদিত (APPROVED) আবেদনের ক্ষেত্রেই কনফার্মেশন ও কুপন কোড মেইল পাঠানো যাবে।');
        }

        if (empty($waiverApplication->email) || !filter_var($waiverApplication->email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'আবেদনকারীর কোনো বৈধ ইমেইল ঠিকানা পাওয়া যায়নি। অনুগ্রহ করে প্রথমে ইমেইল আপডেট করুন।');
        }

        try {
            $emailData = $this->buildApprovalEmailData($waiverApplication);
            $mailService = app(\App\Services\DynamicMailService::class);
            $sent = $mailService->sendHtmlNotification(
                $waiverApplication->email,
                $emailData['subject'],
                $emailData['body'],
                null,
                $emailData['applyUrl']
            );

            if ($sent) {
                return back()->with('success', "আবেদনকারীর ইমেইলে ({$waiverApplication->email}) পুওর ফান্ড কনফার্মেশন মেইল সফলভাবে পুনরায় পাঠানো হয়েছে।");
            } else {
                return back()->with('error', 'ইমেইল পাঠাতে ব্যর্থ হয়েছে। অনুগ্রহ করে SMTP সেটিংস যাচাই করুন।');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Waiver Resend Email Exception: ' . $e->getMessage());
            return back()->with('error', 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Build approval email subject, body, apply URL, and running student note
     */
    public function buildApprovalEmailData(WaiverApplication $waiverApplication): array
    {
        $subject = "🎉 অভিনন্দন: আপনার পুওর ফান্ড আবেদন অনুমোদিত হয়েছে! কুপন কোড: {$waiverApplication->application_no}";

        $detailsList = [];
        $detailsList[] = "• পুওর ফান্ড / কুপন কোড: {$waiverApplication->application_no}";

        if ($waiverApplication->approved_admission_fee !== null) {
            $detailsList[] = "• অনুমোদিত ভর্তি ফি: ৳" . number_format($waiverApplication->approved_admission_fee, 0);
        }

        $poorFundFee = null;
        if (!empty($waiverApplication->approved_package_id)) {
            $pkg = $waiverApplication->approvedPackage ?: CourseFeePackage::find($waiverApplication->approved_package_id);
            if ($pkg) {
                $detailsList[] = "• অনুমোদিত ফি প্যাকেজ: {$pkg->name}";
                if ($pkg->monthly_fee > 0) {
                    $poorFundFee = number_format($pkg->monthly_fee, 0);
                } elseif ($pkg->total > 0) {
                    $poorFundFee = number_format($pkg->total, 0);
                }
            }
        }

        if (!$poorFundFee && !empty($waiverApplication->convenient_monthly_fee) && $waiverApplication->convenient_monthly_fee > 0) {
            $poorFundFee = number_format($waiverApplication->convenient_monthly_fee, 0);
        }

        $poorFundFeeFormatted = $poorFundFee ?: 'নির্ধারিত';
        $detailsText = implode("\n", $detailsList);
        $applyUrl = url('/apply?waiver_code=' . urlencode($waiverApplication->application_no));

        $runningStudentNote = '<div style="margin:24px 0 16px;padding:16px 20px;background:#f0fdf4;border:1px solid #86efac;border-left:5px solid #16a34a;border-radius:8px;font-family:\'Kalpurush\',\'SolaimanLipi\',sans-serif;">'
            . '<div style="font-weight:700;color:#166534;font-size:15px;margin-bottom:6px;">📌 বিশেষ দ্রষ্টব্য (বর্তমান শিক্ষার্থীদের জন্য):</div>'
            . '<div style="color:#14532d;font-size:13.5px;line-height:1.6;">আপনি যদি ইতিমধ্যে আমাদের রানিং/বর্তমান শিক্ষার্থী হয়ে থাকেন, তবে আপনার নতুন করে ভর্তি ফরম পূরণ করার প্রয়োজন নেই। আপনার স্টুডেন্ট পোর্টালে পুর ফান্ডের মাসিক বেতন <strong>' . $poorFundFeeFormatted . '</strong> টাকা আপডেট করে দেওয়া হয়েছে। দয়া করে আপনার পোর্টাল চেক করুন।</div>'
            . '</div>';

        $emailBody = "আসসালামু আলাইকুম {$waiverApplication->full_name},\n\n"
            . "আলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসার প্রশাসন কর্তৃক আপনার পুওর ফান্ড / বিশেষ ছাড়ের আবেদনটি (আবেদন নং: {$waiverApplication->application_no}) সফলভাবে অনুমোদিত হয়েছে।\n\n"
            . "আপনার অনুমোদিত বিশেষ ছাড় ও কুপন কোডের বিবরণ:\n"
            . "----------------------------------------\n"
            . "{$detailsText}\n"
            . "----------------------------------------\n\n"
            . "ভর্তি ফরম পূরণ করার সময় আপনার এই কুপন কোডটি ({$waiverApplication->application_no}) ব্যবহার করে আপনি অনুমোদিত নির্ধারিত ফি-তে ভর্তি সম্পন্ন করতে পারবেন।\n\n"
            . "ভর্তি ফরম পূরণ করতে নিচের লিংকে ক্লিক করুন:\n"
            . "ভর্তি ফরম লিংক: {$applyUrl}\n\n"
            . $runningStudentNote . "\n\n"
            . "বিনীত,\n"
            . "পুওর ফান্ড ও স্কলারশিপ কমিটি,\n"
            . "ইসলামিক অনলাইন মাদ্রাসা (IOM)";

        return [
            'subject'  => $subject,
            'body'     => $emailBody,
            'applyUrl' => $applyUrl,
            'fee'      => $poorFundFeeFormatted,
        ];
    }
}
