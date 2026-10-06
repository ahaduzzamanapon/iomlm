<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\WaiverApplication;
use Illuminate\Http\Request;

class WaiverApplicationController extends Controller
{
    public function show(Request $request, ?string $type = null)
    {
        try {
            $divisions = Division::orderBy('name')->get();
        } catch (\Throwable $e) {
            $divisions = collect();
        }

        try {
            $courses = \App\Models\Course::where('is_active', true)->where('is_poor_fund_applicable', true)->orderBy('name')->get();
        } catch (\Throwable $e) {
            $courses = collect();
        }

        // Determine active waiver type
        $routeName = $request->route()?->getName();
        $queryType = strtolower(trim((string) $request->query('type', '')));

        if ($routeName === 'poor_fund.admission' || $type === 'admission' || in_array($queryType, ['admission', 'admission-fee', 'admission_fee'])) {
            $activeType = 'admission';
        } elseif ($routeName === 'poor_fund.tuition' || $routeName === 'poor_fund.monthly' || in_array($type, ['tuition', 'monthly']) || in_array($queryType, ['tuition', 'monthly', 'tuition-fee', 'monthly-fee'])) {
            $activeType = 'tuition';
        } else {
            $activeType = 'both';
        }

        return view('public.poor_fund', compact('divisions', 'courses', 'activeType'));
    }

    public function showAdmission(Request $request)
    {
        return $this->show($request, 'admission');
    }

    public function showTuition(Request $request)
    {
        return $this->show($request, 'tuition');
    }

    public function showBoth(Request $request)
    {
        return $this->show($request, 'both');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'                      => 'required|string|max:200',
            'email'                          => 'required|email|max:150',
            'phone'                          => 'required|string|max:30',
            'date_of_birth'                  => 'required|date',
            'father_name'                    => 'required|string|max:200',
            'national_id'                    => 'required|string|max:50',
            'gender'                         => 'nullable|in:Male,Female,Other',
            'is_abroad'                      => 'nullable|boolean',
            'division_id'                    => 'required_if:is_abroad,0|nullable|exists:divisions,id',
            'country_name'                   => 'required_if:is_abroad,1|nullable|string|max:100',
            'present_address'                => 'required|string',
            'permanent_address'              => 'required|string',
            'same_as_present'                => 'nullable|boolean',
            'occupation'                     => 'nullable|string|max:100',
            'institution_or_business'        => 'required|string|max:250',
            'is_present_iom_student'         => 'nullable|boolean',
            'student_roll'                   => 'required_if:is_present_iom_student,1|nullable|string|max:50',
            'source_of_income'               => 'required|string|max:100',
            'monthly_income'                 => 'required|numeric|min:0',
            'guardian_phone'                 => 'nullable|string|max:30',
            'is_married'                     => 'nullable|boolean',
            'family_siblings_details'        => 'required|string',
            'financial_problem_description'  => 'required|string',
            'apply_reason_type'              => 'required|in:Admission Fee,Monthly Fee,Both',
            'convenient_admission_fee'       => 'nullable|numeric|min:0',
            'convenient_monthly_fee'         => 'nullable|numeric|min:0',
            'course_id'                      => 'nullable|exists:courses,id',
        ]);

        if (!empty($validated['course_id'])) {
            $selectedCourse = \App\Models\Course::find($validated['course_id']);
            if ($selectedCourse && !$selectedCourse->is_poor_fund_applicable) {
                return back()->withInput()->withErrors([
                    'course_id' => 'দুঃখিত, "' . $selectedCourse->name . '" কোর্সের জন্য পুওর ফান্ড বা স্কলারশিপ আবেদন প্রযোজ্য নয়।'
                ]);
            }
        }

        // Map old apply_reason_type values to new apply_for enum
        $applyForMap = [
            'Admission Fee' => 'ADMISSION_FEE',
            'Monthly Fee'   => 'TUITION_FEE',
            'Both'          => 'BOTH',
        ];
        $applyFor = $applyForMap[$validated['apply_reason_type']] ?? 'BOTH';

        $app = WaiverApplication::create([
            'application_no'                 => WaiverApplication::generateApplicationNo(),
            'full_name'                      => $validated['full_name'],
            'email'                          => $validated['email'],
            'phone'                          => $validated['phone'],
            'date_of_birth'                  => $validated['date_of_birth'],
            'father_name'                    => $validated['father_name'],
            'national_id'                    => $validated['national_id'],
            'gender'                         => $validated['gender'] ?? null,
            'is_abroad'                      => $request->boolean('is_abroad'),
            'division_id'                    => $validated['division_id'] ?? null,
            'country_name'                   => $validated['country_name'] ?? null,
            'present_address'                => $validated['present_address'],
            'permanent_address'              => $validated['permanent_address'],
            'same_as_present'                => $request->boolean('same_as_present'),
            'occupation'                     => $validated['occupation'] ?? null,
            'institution_or_business'        => $validated['institution_or_business'],
            'is_present_iom_student'         => $request->boolean('is_present_iom_student'),
            'student_roll'                   => $validated['student_roll'] ?? null,
            'source_of_income'               => $validated['source_of_income'],
            'monthly_income'                 => $validated['monthly_income'],
            'guardian_phone'                 => $validated['guardian_phone'] ?? null,
            'is_married'                     => $request->boolean('is_married'),
            'family_siblings_details'        => $validated['family_siblings_details'],
            'financial_problem_description'  => $validated['financial_problem_description'],
            'apply_reason_type'              => $validated['apply_reason_type'],
            'apply_for'                      => $applyFor,
            'convenient_admission_fee'       => $validated['convenient_admission_fee'] ?? 0,
            'convenient_monthly_fee'         => $validated['convenient_monthly_fee'] ?? 0,
            'course_id'                      => $validated['course_id'] ?? null,
            'status'                         => 'PENDING',
            'ip_address'                     => $request->ip(),
        ]);

        return redirect()->route('poor_fund.success', $app->application_no);
    }

    public function success(string $applicationNo)
    {
        $app = WaiverApplication::where('application_no', $applicationNo)->firstOrFail();
        return view('public.poor_fund_success', compact('app'));
    }

    public function lookup(Request $request)
    {
        $code = strtoupper(trim($request->query('code', '')));
        if (!$code) {
            return response()->json([
                'valid'   => false,
                'message' => 'অনুরোধ: অনুগ্রহ করে আপনার পুওর ফান্ড কোডটি লিখুন।'
            ], 422);
        }

        $targetCourseId = $request->query('course_id');
        $app = WaiverApplication::where('application_no', $code)->first();

        if (!$app) {
            // Check if code matches a Course-wise Manual Coupon Code
            $couponQuery = \App\Models\CourseCoupon::with('course')->where('code', $code);
            if ($targetCourseId) {
                $coupon = (clone $couponQuery)->where('course_id', $targetCourseId)->first()
                    ?? $couponQuery->first();
            } else {
                $coupon = $couponQuery->first();
            }

            if ($coupon) {
                $validation = $coupon->validateForCourse($targetCourseId ? (int) $targetCourseId : null);
                if (!$validation['valid']) {
                    return response()->json([
                        'valid'   => false,
                        'type'    => 'COUPON',
                        'message' => $validation['message'],
                    ], 422);
                }

                $discText = ($coupon->discount_type === 'PERCENT')
                    ? "{$coupon->discount_amount}% ছাড়"
                    : "৳" . number_format($coupon->discount_amount, 0) . " টাকা ছাড়";

                return response()->json([
                    'valid'                     => true,
                    'type'                      => 'COUPON',
                    'status'                    => 'ACTIVE',
                    'code'                      => $coupon->code,
                    'course_id'                 => $coupon->course_id,
                    'discount_type'             => $coupon->discount_type,
                    'discount_amount'           => (float) $coupon->discount_amount,
                    'discount_percent'          => $coupon->discount_type === 'PERCENT' ? (float) $coupon->discount_amount : 0,
                    'approved_admission_fee'    => null,
                    'message'                   => "✓ কুপন কোড '{$coupon->code}' সফলভাবে সক্রিয় হয়েছে! ({$discText})",
                ]);
            }

            return response()->json([
                'valid'   => false,
                'message' => '✕ এই কুপন বা ছাড় কোডটি ('.$code.') সঠিক নয়। (Code Not Found)'
            ], 404);
        }

        if ($app->is_used) {
            return response()->json([
                'valid'   => false,
                'status'  => 'USED',
                'message' => '✕ এই পুওর ফান্ড কোডটি ('.$code.') ইতোমধ্যে একবার ভর্তি ফর্মে ব্যবহার করা হয়ে গেছে। (Code Already Used)'
            ], 422);
        }

        if ($app->status === 'PENDING') {
            return response()->json([
                'valid'   => false,
                'status'  => 'PENDING',
                'message' => '⏳ আপনার পুওর ফান্ড আবেদনটি ('.$code.') এখনও কমিটির পর্যালোচনায় রয়েছে (Pending Committee Approval)। অনুমোদিত হওয়ার পর এই কোড ব্যবহার করতে পারবেন।'
            ], 422);
        }

        if ($app->status === 'REJECTED') {
            return response()->json([
                'valid'   => false,
                'status'  => 'REJECTED',
                'message' => '✕ আপনার পুওর ফান্ড আবেদনটি ('.$code.') গৃহিত হয়নি (Not Approved)। নোট: '.($app->reviewer_notes ?? 'শর্তাবলী পূরণ হয়নি।')
            ], 422);
        }

        // Check if course is poor fund applicable
        if ($app->course_id) {
            $linkedCourse = \App\Models\Course::find($app->course_id);
            if ($linkedCourse && !$linkedCourse->is_poor_fund_applicable) {
                return response()->json([
                    'valid'   => false,
                    'status'  => 'NOT_APPLICABLE',
                    'message' => '✕ দুঃখিত, "' . $linkedCourse->name . '" কোর্সের জন্য পুওর ফান্ড স্কলারশিপ প্রযোজ্য নয়।'
                ], 422);
            }
        }

        $targetCourseId = $request->query('course_id');
        if ($targetCourseId) {
            $targetCourse = \App\Models\Course::find($targetCourseId);
            if ($targetCourse && !$targetCourse->is_poor_fund_applicable) {
                return response()->json([
                    'valid'   => false,
                    'status'  => 'NOT_APPLICABLE',
                    'message' => '✕ দুঃখিত, "' . $targetCourse->name . '" কোর্সের জন্য পুওর ফান্ড স্কলারশিপ কোড প্রযোজ্য নয়।'
                ], 422);
            }
        }

        // Build approval message based on apply_for type
        $applyFor = $app->apply_for ?? 'BOTH';
        $msgParts = [];
        if (in_array($applyFor, ['ADMISSION_FEE', 'BOTH']) && $app->approved_admission_fee !== null) {
            $msgParts[] = 'ভর্তি ফি: ৳' . number_format($app->approved_admission_fee, 0);
        }
        if (in_array($applyFor, ['TUITION_FEE', 'BOTH']) && $app->approved_package_id) {
            $pkg = \App\Models\CourseFeePackage::find($app->approved_package_id);
            $msgParts[] = 'Package: ' . ($pkg?->name ?? 'Selected');
        }
        $discText = implode(' | ', $msgParts) ?: 'Waiver Approved';

        // APPROVED & NOT USED!
        return response()->json([
            'valid'                     => true,
            'type'                      => 'POOR_FUND',
            'status'                    => 'APPROVED',
            'apply_for'                 => $applyFor,
            'application_no'            => $app->application_no,
            'approved_admission_fee'    => $app->approved_admission_fee,
            'approved_package_id'       => $app->approved_package_id,
            // Legacy fields (kept for backward compat with apply form)
            'discount_type'             => $app->discount_type ?? 'PERCENTAGE',
            'approved_discount_value'   => $app->approved_discount_value ?? 0,
            'approved_discount_percent' => $app->approved_discount_percent ?? 0,
            'full_name'                 => $app->full_name,
            'email'                     => $app->email,
            'phone'                     => $app->phone,
            'date_of_birth'             => $app->date_of_birth ? \Carbon\Carbon::parse($app->date_of_birth)->format('Y-m-d') : null,
            'father_name'               => $app->father_name,
            'national_id'               => $app->national_id,
            'gender'                    => $app->gender,
            'division_id'               => $app->division_id,
            'present_address'           => $app->present_address,
            'permanent_address'         => $app->permanent_address,
            'occupation'                => $app->occupation,
            'guardian_phone'            => $app->guardian_phone,
            'message'                   => "✓ অভিনন্দন! আপনার পুওর ফান্ড কোডটি অনুমোদিত (Approved)। {$discText} — ফর্মের তথ্যগুলো অটো-ফিল করা হয়েছে।",
        ]);
    }

    /**
     * Public Poor Fund Application Tracker View
     */
    public function trackStatus(?Request $request = null)
    {
        $request = $request ?? request();
        $searchQuery = trim((string) $request->query('app_no', $request->query('id', '')));
        $selectedId = $request->query('selected_id');
        $applications = collect();
        $application = null;

        if (!empty($searchQuery)) {
            $applications = WaiverApplication::with(['division', 'course', 'approvedPackage'])
                ->where(function ($q) use ($searchQuery) {
                    $q->where('application_no', $searchQuery)
                      ->orWhere('application_no', 'like', "%{$searchQuery}%")
                      ->orWhere('phone', $searchQuery)
                      ->orWhere('national_id', $searchQuery)
                      ->orWhere('email', $searchQuery);
                })
                ->latest('id')
                ->get();

            if ($selectedId) {
                $application = $applications->firstWhere('id', (int) $selectedId);
            } elseif ($applications->contains('application_no', $searchQuery)) {
                $application = $applications->firstWhere('application_no', $searchQuery);
            } elseif ($applications->isNotEmpty()) {
                $application = $applications->first();
            }
        }

        return view('public.poor_fund_track', compact('applications', 'application', 'searchQuery'));
    }

    /**
     * Public Poor Fund Tracker POST Lookup
     */
    public function trackStatusLookup(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:100',
        ], [
            'search.required' => 'আবেদন আইডি (উদা: PF-2026-0001), ফোন নম্বর বা এনআইডি প্রবেশ করান।'
        ]);

        $searchQuery = trim((string) $request->input('search'));
        return redirect()->route('poor_fund.status', ['app_no' => $searchQuery]);
    }
}
