<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AdmissionCircular;
use App\Models\AdmissionForm;
use App\Models\AppSetting;
use App\Models\Batch;
use App\Models\BloodGroup;
use App\Models\Course;
use App\Models\District;
use App\Models\Division;
use App\Models\GatewayTransaction;
use App\Models\Religion;
use App\Models\Student;
use App\Models\WaiverApplication;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdmissionFormController extends Controller
{
    public function show()
    {
        $terms = AppSetting::get('admission_terms', '');

        // Fetch current active circular
        $activeCircular = AdmissionCircular::with(['circularBatches.course', 'circularBatches.batch'])
            ->where('circular_status', 'Current')
            ->where('is_enabled', true)
            ->latest('id')
            ->first();

        $admissionOpen = true;

        if ($activeCircular) {
            if ($activeCircular->is_program_batch_map_enabled) {
                $enabledCourseIds = $activeCircular->circularBatches
                    ->where('is_online_admission_enabled', true)
                    ->pluck('course_id');

                $courses = Course::where('is_active', true)->whereIn('id', $enabledCourseIds)->orderBy('name')->get();
                $enabledBatchIds = $activeCircular->circularBatches
                    ->where('is_online_admission_enabled', true)
                    ->pluck('batch_id')
                    ->filter();

                if ($enabledBatchIds->isNotEmpty()) {
                    $activeBatches = Batch::where('status', 'ACTIVE')->whereIn('id', $enabledBatchIds)->with('course')->get();
                } else {
                    $activeBatches = Batch::where('status', 'ACTIVE')->whereIn('course_id', $enabledCourseIds)->with('course')->get();
                }

                if ($courses->isEmpty()) {
                    $admissionOpen = false;
                }
            } else {
                $courses = Course::where('is_active', true)->orderBy('name')->get();
                $activeBatches = Batch::where('status', 'ACTIVE')->with('course')->get();
            }
        } else {
            $admissionOpen = false;
            $courses = collect();
            $activeBatches = collect();
        }

        $sessions = AcademicSession::where('is_active', true)->orderByDesc('id')->get();
        $bloodGroups = BloodGroup::active()->get();
        $religions = Religion::active()->get();
        $divisions = Division::orderBy('name')->get();

        $sslActive = PaymentGatewayService::isSslcommerzActive();
        $bkashActive = PaymentGatewayService::isBkashActive();

        $coursesByDepartment = $courses->groupBy(function ($c) {
            return $c->department ?: 'General Courses';
        });

        return view('apply.index', compact(
            'terms',
            'courses',
            'coursesByDepartment',
            'activeBatches',
            'sessions',
            'bloodGroups',
            'religions',
            'divisions',
            'sslActive',
            'bkashActive',
            'activeCircular',
            'admissionOpen'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'nullable|exists:batches,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'applicant_name' => 'required|string|max:200',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'gender' => 'required|in:Male,Female,Other,male,female,other',
            'terms_agreed' => 'required',
        ], [
            'terms_agreed.required' => 'মাদ্রাসার নিয়ম ও ভর্তির শর্তাবলীতে সম্মতি প্রদান করা আবশ্যক।'
        ]);

        // Check active circular
        $activeCircular = AdmissionCircular::with('circularBatches')
            ->where('circular_status', 'Current')
            ->where('is_enabled', true)
            ->latest('id')
            ->first();

        if (!$activeCircular) {
            return back()->withInput()->with('error', 'বর্তমানে কোনো ভর্তি সেশন সক্রিয় নেই। অনুগ্রহ করে পরবর্তীতে যোগাযোগ করুন।');
        }

        if ($activeCircular->is_program_batch_map_enabled) {
            $batchSetting = $activeCircular->circularBatches->where('course_id', $validated['course_id'])->first();
            if (!$batchSetting || !$batchSetting->is_online_admission_enabled) {
                return back()->withInput()->with('error', 'নির্বাচিত কোর্সে বর্তমানে অনলাইন ভর্তি বন্ধ রয়েছে।');
            }
            if (empty($validated['batch_id']) && !empty($batchSetting->batch_id)) {
                $validated['batch_id'] = $batchSetting->batch_id;
            }
        }

        $course = Course::findOrFail($validated['course_id']);

        // Check if student with this email is already actively enrolled in this course
        $existingStudent = !empty($validated['email'])
            ? Student::where('email', $validated['email'])->first()
            : null;

        if ($existingStudent) {
            $alreadyEnrolled = $existingStudent->enrollments()
                ->where('course_id', $validated['course_id'])
                ->where('status', 'ACTIVE')
                ->exists();

            if ($alreadyEnrolled) {
                return back()->withInput()->with('error', 'আপনি ইতিমধ্যে এই কোর্সে সক্রিয়ভাবে ভর্তি আছেন। অনুগ্রহ করে লগইন করে আপনার ড্যাশবোর্ডে প্রবেশ করুন।');
            }
        }

        // Create Application & Lead Student inside Transaction
        $result = DB::transaction(function () use ($validated, $request, $existingStudent, $activeCircular) {
            $sessionId = $validated['academic_session_id']
                ?? AcademicSession::where('is_active', true)->orderByDesc('id')->value('id');

            // 1. Find or Create Student as LEAD
            $student = $existingStudent;
            if (!$student && !empty($validated['phone'])) {
                $student = Student::where('phone', $validated['phone'])->first();
            }

            if ($student) {
                $student->update([
                    'name' => $validated['applicant_name'],
                    'phone' => $validated['phone'] ?: $student->phone,
                    'gender' => $validated['gender'] ?: $student->gender,
                ]);
            } else {
                $student = Student::create([
                    'name' => $validated['applicant_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'status' => 'LEAD',
                ]);
            }

            $student->calculateProfileCompletion();

            // 2. Check if student already has an unpaid PENDING admission form for this course
            $form = AdmissionForm::where('student_id', $student->id)
                ->where('interested_course_id', $validated['course_id'])
                ->where('status', 'PENDING')
                ->latest()
                ->first();

            if ($form) {
                // Update batch / session on existing pending application
                $form->update([
                    'admission_circular_id' => $activeCircular->id,
                    'batch_id' => $validated['batch_id'] ?? $form->batch_id,
                    'academic_session_id' => $sessionId,
                    'ip_address' => $request->ip(),
                ]);
            } else {
                $form = AdmissionForm::create([
                    'source' => 'PUBLIC',
                    'application_no' => AdmissionForm::generateApplicationNo(),
                    'student_id' => $student->id,
                    'admission_circular_id' => $activeCircular->id,
                    'interested_course_id' => $validated['course_id'],
                    'batch_id' => $validated['batch_id'] ?? null,
                    'academic_session_id' => $sessionId,
                    'attempt_no' => 1,
                    'lead_source' => 'Website',
                    'status' => 'PENDING',
                    'ip_address' => $request->ip(),
                ]);
            }

            return ['form' => $form, 'student' => $student];
        });

        $form = $result['form'];

        // Redirect directly to the dedicated Step 2 Payment / Checkout page!
        return redirect()->route('apply.payment', $form->application_no);
    }

    /**
     * Step 2: Dedicated Payment & Checkout Page
     */
    public function paymentView(string $applicationNo)
    {
        $form = AdmissionForm::with(['interestedCourse', 'session', 'student', 'batch'])
            ->where('application_no', $applicationNo)
            ->where('source', 'PUBLIC')
            ->firstOrFail();

        if ($form->status === 'APPROVED') {
            return redirect()->route('apply.success', $form->application_no);
        }

        $course = $form->interestedCourse;
        $batch = $form->batch;

        // Base Fee calculation: prioritize batch fee if > 0, otherwise course admission fee
        $courseFee = (float) ($course->admission_fee ?? 0);
        $batchFee = ($batch && (float) $batch->admission_fee > 0) ? (float) $batch->admission_fee : $courseFee;
        $baseFee = max(0, $batchFee);

        $discountAmount = (float) ($form->discount_amount ?? 0);
        $netPayable = max(0, round($baseFee - $discountAmount, 2));

        $sslActive = PaymentGatewayService::isSslcommerzActive();
        $bkashActive = PaymentGatewayService::isBkashActive();

        return view('apply.payment', compact('form', 'course', 'batch', 'baseFee', 'discountAmount', 'netPayable', 'sslActive', 'bkashActive'));
    }

    /**
     * Process Admission Payment (SSLCommerz / bKash / Free)
     */
    public function processPayment(Request $request, string $applicationNo)
    {
        $form = AdmissionForm::with(['interestedCourse', 'session', 'student', 'batch'])
            ->where('application_no', $applicationNo)
            ->where('source', 'PUBLIC')
            ->firstOrFail();

        if ($form->status === 'APPROVED') {
            return redirect()->route('apply.success', $form->application_no);
        }

        $course = $form->interestedCourse;
        $batch = $form->batch;

        // Base Fee calculation
        $courseFee = (float) ($course->admission_fee ?? 0);
        $batchFee = ($batch && (float) $batch->admission_fee > 0) ? (float) $batch->admission_fee : $courseFee;
        $baseFee = max(0, $batchFee);

        // Check Waiver / Coupon Code
        $discountAmount = 0.0;
        $discountPercent = 0.0;
        $waiverCode = null;

        if ($request->filled('waiver_code')) {
            if (!$course->is_poor_fund_applicable) {
                return back()->withInput()->with('error', 'দুঃখিত, "' . $course->name . '" কোর্সের জন্য পুওর ফান্ড বা স্কলারশিপ কোড প্রযোজ্য নয়।');
            }

            $code = strtoupper(trim($request->input('waiver_code')));
            $altCode = str_starts_with($code, 'PF-')
                ? str_replace('PF-', 'POOR-', $code)
                : (str_starts_with($code, 'POOR-') ? str_replace('POOR-', 'PF-', $code) : $code);

            $waiverApp = WaiverApplication::where(function ($q) use ($code, $altCode) {
                    $q->where('application_no', $code)->orWhere('application_no', $altCode);
                })
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($form) {
                    $q->where('is_used', false)->orWhere('admission_form_id', $form->id);
                })
                ->first();

            if ($waiverApp) {
                $waiverCode = $waiverApp->application_no;
                if ($waiverApp->approved_admission_fee !== null && in_array($waiverApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
                    $approvedFee = (float) $waiverApp->approved_admission_fee;
                    $payableAmount = min($baseFee, $approvedFee);
                    $discountAmount = max(0, $baseFee - $payableAmount);
                } else {
                    $discountPercent = (float) ($waiverApp->approved_discount_percent ?? 0);
                    $discountAmount = round(($baseFee * $discountPercent) / 100, 2);
                }

                $waiverApp->update([
                    'is_used' => true,
                    'admission_form_id' => $form->id,
                ]);
            } else {
                return back()->withInput()->with('error', 'প্রদত্ত কুপন বা ছাড় কোডটি সঠিক নয় অথবা ইতিমধ্যে ব্যবহৃত।');
            }
        }

        $netPayable = max(0, round($baseFee - $discountAmount, 2));

        $form->update([
            'waiver_code' => $waiverCode,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
        ]);

        $student = $form->student;
        $sslActive = PaymentGatewayService::isSslcommerzActive();
        $bkashActive = PaymentGatewayService::isBkashActive();

        if ($netPayable > 0 && ($sslActive || $bkashActive)) {
            $chosenGateway = $request->input('payment_gateway');
            if (empty($chosenGateway) || !in_array($chosenGateway, ['sslcommerz', 'bkash'])) {
                $chosenGateway = $sslActive ? 'sslcommerz' : 'bkash';
            }

            $gatewayMode = ($chosenGateway === 'bkash')
                ? PaymentGatewayService::getBkashConfig()['mode']
                : PaymentGatewayService::getSslcommerzConfig()['mode'];

            $transaction = GatewayTransaction::create([
                'tran_id' => GatewayTransaction::generateTranId('ADM'),
                'gateway' => $chosenGateway,
                'gateway_mode' => $gatewayMode,
                'admission_form_id' => $form->id,
                'student_id' => $student->id,
                'amount' => $netPayable,
                'currency' => 'BDT',
                'customer_name' => $student->name,
                'customer_phone' => $student->phone,
                'customer_email' => $student->email,
                'status' => 'INITIATED',
                'ip_address' => $request->ip(),
            ]);

            if ($chosenGateway === 'sslcommerz') {
                $initRes = PaymentGatewayService::initiateSslcommerz(
                    $transaction,
                    $form,
                    $student->phone,
                    $student->email,
                    $student->name
                );
            } else {
                $initRes = PaymentGatewayService::initiateBkash(
                    $transaction,
                    $form,
                    $student->phone
                );
            }

            if (!empty($initRes['success']) && !empty($initRes['redirect_url'])) {
                return redirect()->away($initRes['redirect_url']);
            }

            Log::error("Payment Initiation Failed for {$chosenGateway}: " . ($initRes['message'] ?? ''));
            return redirect()->route('payment.status', $transaction->tran_id)
                ->with('error', $initRes['message'] ?? 'পেমেন্ট গেটওয়েতে সংযোগ করতে ত্রুটি হয়েছে।');
        }

        // 100% Free / Full Waiver Admission
        PaymentGatewayService::approvePaidAdmission($form, null);
        return redirect()->route('apply.success', $form->application_no)
            ->with('success', 'আপনার ভর্তি আবেদন ও স্কলারশিপ সফলভাবে নিশ্চিত হয়েছে!');
    }

    public function success(string $applicationNo)
    {
        $form = AdmissionForm::with(['interestedCourse', 'session', 'student', 'batch'])
            ->where('application_no', $applicationNo)
            ->where('source', 'PUBLIC')
            ->firstOrFail();

        $transaction = GatewayTransaction::where('admission_form_id', $form->id)
            ->latest()
            ->first();

        $instituteName = AppSetting::get('institute_name', 'Islamic Online Madrasah');
        $instituteTagline = AppSetting::get('institute_tagline', 'Through Knowledge, Towards Jannah');

        return view('apply.success', compact('form', 'transaction', 'instituteName', 'instituteTagline'));
    }

    // AJAX: districts by division
    public function districts(Request $request)
    {
        $districts = District::where('division_id', $request->query('division_id'))
            ->orderBy('name')->get(['id', 'name']);
        return response()->json($districts);
    }

    /**
     * Public Applicant Tracker View
     */
    public function trackStatus(Request $request)
    {
        $searchQuery = trim($request->query('app_no', ''));
        $admission = null;
        $isPaid = false;

        if (!empty($searchQuery)) {
            $admission = AdmissionForm::with(['student', 'interestedCourse', 'batch', 'reviewer', 'circular'])
                ->where('application_no', $searchQuery)
                ->orWhereHas('student', function ($q) use ($searchQuery) {
                    $q->where('phone', $searchQuery)->orWhere('student_code', $searchQuery);
                })
                ->latest('id')
                ->first();

            if ($admission) {
                $isPaid = GatewayTransaction::where('admission_form_id', $admission->id)
                    ->where('status', 'SUCCESS')
                    ->exists()
                    || \App\Models\Invoice::where('source_type', AdmissionForm::class)
                    ->where('source_id', $admission->id)
                    ->where('status', 'PAID')
                    ->exists()
                    || ($admission->interestedCourse && $admission->interestedCourse->admission_fee == 0);
            }
        }

        return view('apply.track', compact('admission', 'searchQuery', 'isPaid'));
    }

    /**
     * Public Applicant Tracker POST Lookup
     */
    public function trackStatusLookup(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:100',
        ], [
            'search.required' => 'আবেদন নম্বর বা ফোন নম্বর প্রবেশ করান।'
        ]);

        $searchQuery = trim($request->input('search'));
        return redirect()->route('admission.status', ['app_no' => $searchQuery]);
    }
}
