<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AdmissionForm;
use App\Models\AppSetting;
use App\Models\Batch;
use App\Models\BloodGroup;
use App\Models\Course;
use App\Models\District;
use App\Models\Division;
use App\Models\Enrollment;
use App\Models\GatewayTransaction;
use App\Models\Invoice;
use App\Models\Religion;
use App\Models\Student;
use App\Models\User;
use App\Models\WaiverApplication;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdmissionFormController extends Controller
{
    public function show()
    {
        $terms = AppSetting::get('admission_terms', '');

        // Fetch active batches where admission is open
        $activeBatches = Batch::where('status', 'ACTIVE')
            ->where('is_admission_open', true)
            ->with('course')
            ->get();

        $openCourseIds = $activeBatches->pluck('course_id')->unique()->filter();

        $courses = Course::where('is_active', true)
            ->whereIn('id', $openCourseIds)
            ->orderBy('name')
            ->get();

        $admissionOpen = $courses->isNotEmpty() && $activeBatches->isNotEmpty();

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
            'photo' => 'nullable|image|max:3072',
            'avatar_preset' => 'nullable|string|max:255',
        ], [
            'terms_agreed.required' => 'মাদ্রাসার নিয়ম ও ভর্তির শর্তাবলীতে সম্মতি প্রদান করা আবশ্যক।'
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // Check if this course has an active batch with admission open
        $openBatchQuery = Batch::where('course_id', $course->id)
            ->where('status', 'ACTIVE')
            ->where('is_admission_open', true);

        if (!$openBatchQuery->exists()) {
            return back()->withInput()->with('error', 'নির্বাচিত কোর্সে বর্তমানে ভর্তি বন্ধ রয়েছে।');
        }

        if (!empty($validated['batch_id'])) {
            $batch = (clone $openBatchQuery)->where('id', $validated['batch_id'])->first();
            if (!$batch) {
                return back()->withInput()->with('error', 'নির্বাচিত ব্যাচে বর্তমানে ভর্তি বন্ধ রয়েছে।');
            }
        } else {
            $firstOpenBatch = (clone $openBatchQuery)->first();
            if ($firstOpenBatch) {
                $validated['batch_id'] = $firstOpenBatch->id;
            }
        }

        // Check if student with this email or phone is already actively enrolled in this exact course
        $alreadyEnrolled = Enrollment::where('course_id', $validated['course_id'])
            ->where('status', 'ACTIVE')
            ->whereHas('student', function ($q) use ($validated) {
                $q->where(function ($sub) use ($validated) {
                    if (!empty($validated['phone'])) {
                        $sub->where('phone', $validated['phone']);
                    }
                    if (!empty($validated['email'])) {
                        $sub->orWhere('email', $validated['email']);
                    }
                });
            })
            ->exists();

        if ($alreadyEnrolled) {
            return back()->withInput()->with('error', 'আপনি ইতিমধ্যে এই কোর্সে সক্রিয়ভাবে ভর্তি আছেন। অনুগ্রহ করে লগইন করে আপনার ড্যাশবোর্ডে প্রবেশ করুন।');
        }

        // Create Application & Lead Student inside Transaction
        $result = DB::transaction(function () use ($validated, $request) {
            $sessionId = (!empty($validated['academic_session_id']) && AcademicSession::where('id', $validated['academic_session_id'])->where('is_active', true)->exists())
                ? (int) $validated['academic_session_id']
                : (AcademicSession::getActiveSession()?->id ?? AcademicSession::where('is_active', true)->latest('id')->value('id') ?? AcademicSession::latest('id')->value('id'));

            // Resolve student photo or avatar preset
            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos/students', 'public');
                $photoUrl = '/storage/' . $path;
            } elseif (!empty($validated['avatar_preset'])) {
                $photoUrl = $validated['avatar_preset'];
            } else {
                $photoUrl = Student::defaultAvatarForGender($validated['gender'] ?? null);
            }

            // 1. Find or Create Student as LEAD for this specific course
            $targetCourseId = (int) $validated['course_id'];
            $student = Student::where(function ($q) use ($validated) {
                if (!empty($validated['phone'])) {
                    $q->where('phone', $validated['phone']);
                }
                if (!empty($validated['email'])) {
                    $q->orWhere('email', $validated['email']);
                }
            })->where(function ($q) use ($targetCourseId) {
                $q->whereHas('admissionForms', fn($af) => $af->where('interested_course_id', $targetCourseId))
                  ->orWhereHas('enrollments', fn($en) => $en->where('course_id', $targetCourseId));
            })->first();

            if ($student) {
                $updateData = [
                    'name'   => $validated['applicant_name'],
                    'phone'  => $validated['phone'] ?: $student->phone,
                    'gender' => $validated['gender'] ?: $student->gender,
                ];
                if ($photoUrl) {
                    $updateData['photo_url'] = $photoUrl;
                }
                $student->update($updateData);
            } else {
                // If applicant had an earlier student record (for another course), inherit their profile info
                $prevStudent = Student::where(function ($q) use ($validated) {
                    if (!empty($validated['phone'])) {
                        $q->where('phone', $validated['phone']);
                    }
                    if (!empty($validated['email'])) {
                        $q->orWhere('email', $validated['email']);
                    }
                })->latest('id')->first();

                $studentData = [
                    'name'      => $validated['applicant_name'],
                    'phone'     => $validated['phone'],
                    'email'     => $validated['email'] ?? null,
                    'gender'    => $validated['gender'] ?? null,
                    'photo_url' => $photoUrl,
                    'status'    => 'LEAD',
                ];

                if ($prevStudent) {
                    foreach (['date_of_birth', 'blood_group', 'national_id', 'address', 'father_name', 'mother_name', 'guardian_name', 'guardian_phone'] as $fld) {
                        if (empty($studentData[$fld]) && !empty($prevStudent->{$fld})) {
                            $studentData[$fld] = $prevStudent->{$fld};
                        }
                    }
                    if (empty($studentData['photo_url']) && !empty($prevStudent->photo_url)) {
                        $studentData['photo_url'] = $prevStudent->photo_url;
                    }
                }

                $student = Student::create($studentData);
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
                    'batch_id' => $validated['batch_id'] ?? $form->batch_id,
                    'academic_session_id' => $sessionId,
                    'gender' => $validated['gender'] ?? $form->gender,
                    'ip_address' => $request->ip(),
                ]);
            } else {
                $form = AdmissionForm::create([
                    'source' => 'PUBLIC',
                    'application_no' => AdmissionForm::generateApplicationNo(),
                    'student_id' => $student->id,
                    'interested_course_id' => $validated['course_id'],
                    'batch_id' => $validated['batch_id'] ?? null,
                    'academic_session_id' => $sessionId,
                    'gender' => $validated['gender'] ?? null,
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
            $code = strtoupper(trim($request->input('waiver_code')));
            $altCode = str_starts_with($code, 'PF-')
                ? str_replace('PF-', 'POOR-', $code)
                : (str_starts_with($code, 'POOR-') ? str_replace('POOR-', 'PF-', $code) : $code);

            // 1. Try matching an approved WaiverApplication (Poor Fund)
            $waiverApp = WaiverApplication::where(function ($q) use ($code, $altCode) {
                    $q->where('application_no', $code)->orWhere('application_no', $altCode);
                })
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($form) {
                    $q->where('is_used', false)->orWhere('admission_form_id', $form->id);
                })
                ->first();

            if ($waiverApp) {
                if (!$course->is_poor_fund_applicable) {
                    return back()->withInput()->with('error', 'দুঃখিত, "' . $course->name . '" কোর্সের জন্য পুওর ফান্ড বা স্কলারশিপ কোড প্রযোজ্য নয়।');
                }

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
                // 2. Try matching a Course-wise Manual Coupon Code
                $coupon = \App\Models\CourseCoupon::where('course_id', $course->id)
                    ->where('code', $code)
                    ->first();

                if ($coupon) {
                    $validation = $coupon->validateForCourse($course->id);
                    if (!$validation['valid']) {
                        return back()->withInput()->with('error', $validation['message']);
                    }

                    $waiverCode = $coupon->code;
                    $discountAmount = $coupon->calculateDiscount($baseFee);

                    if ($coupon->discount_type === 'PERCENT') {
                        $discountPercent = (float) $coupon->discount_amount;
                    } else {
                        $discountPercent = $baseFee > 0 ? round(($discountAmount / $baseFee) * 100, 2) : 0;
                    }

                    // Increment coupon usage
                    $coupon->increment('used_count');
                } else {
                    return back()->withInput()->with('error', 'প্রদত্ত কুপন বা ছাড় কোডটি সঠিক নয় অথবা মেয়াদোত্তীর্ণ/ব্যবহৃত।');
                }
            }
        }

        $netPayable = max(0, round($baseFee - $discountAmount, 2));

        $form->update([
            'waiver_code' => $waiverCode,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
        ]);

        $student = $form->student;

        // Prior / Manual Merchant Payment Handling
        if ($netPayable > 0 && $request->input('payment_gateway') === 'manual') {
            $submittedTrxId = strtoupper(trim((string)$request->input('manual_trx_id', '')));
            $paymentMethod = trim((string)$request->input('manual_payment_method', ''));

            $request->validate([
                'manual_payment_method' => 'required|string|max:50',
                'manual_sender_phone'   => 'required|string|max:30',
                'manual_paid_amount'    => 'required|numeric|min:1',
                'manual_trx_id'         => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($form, $paymentMethod, $submittedTrxId) {
                        // 1. bKash 10-character validation (Requirement 4)
                        if (strtolower($paymentMethod) === 'bkash') {
                            if (strlen($submittedTrxId) !== 10) {
                                $fail('বিকাশের ট্রাঞ্জেকশন আইডি (TrxID) অবশ্যই সুনির্দিষ্ট ১০ ডিজিট/অক্ষরের হতে হবে (বর্তমানে ' . strlen($submittedTrxId) . ' অক্ষর দেওয়া হয়েছে)।');
                                return;
                            }
                            if (!preg_match('/^[A-Z0-9]{10}$/', $submittedTrxId)) {
                                $fail('বিকাশের ট্রাঞ্জেকশন আইডি শুধুমাত্র ইংরেজি অক্ষর ও সংখ্যা মিলিয়ে ১০ অক্ষরের হতে হবে।');
                                return;
                            }
                        } else {
                            if (strlen($submittedTrxId) < 6 || strlen($submittedTrxId) > 30) {
                                $fail('ট্রাঞ্জেকশন আইডি সঠিক ফরম্যাটে প্রদান করুন।');
                                return;
                            }
                        }

                        // 2. Prevent duplicate TrxID (Requirement 3)
                        $isDuplicate = AdmissionForm::where('manual_trx_id', $submittedTrxId)
                            ->where('id', '!=', $form->id)
                            ->exists()
                            || GatewayTransaction::where('gateway_trx_id', $submittedTrxId)->exists()
                            || GatewayTransaction::where('tran_id', $submittedTrxId)->exists();

                        if ($isDuplicate) {
                            $fail("এই ট্রাঞ্জেকশন আইডিটি ({$submittedTrxId}) ইতিমধ্যে সিস্টেমে অন্য একটি আবেদনের জন্য ব্যবহৃত হয়েছে। একই TrxID দিয়ে একাধিকবার আবেদন করা যাবে না।");
                        }
                    },
                ],
                'manual_payment_notes'  => 'nullable|string|max:500',
            ], [
                'manual_payment_method.required' => 'পেমেন্ট মাধ্যম (বিকাশ/নগদ/রকেট/ব্যাংক) নির্বাচন করুন।',
                'manual_trx_id.required'         => 'ট্রাঞ্জেকশন আইডি (TrxID) প্রদান করা আবশ্যক।',
                'manual_sender_phone.required'   => 'যে নম্বর থেকে পেমেন্ট পাঠিয়েছেন সেই নম্বরটি লিখুন।',
                'manual_paid_amount.required'    => 'কত টাকা পেমেন্ট করেছেন তা উল্লেখ করুন।',
                'manual_paid_amount.numeric'     => 'পরিশোধিত টাকার পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
                'manual_paid_amount.min'         => 'পরিশোধিত টাকার পরিমাণ কমপক্ষে ১ টাকা হতে হবে।',
            ]);

            $wasTrashed = in_array($form->status, ['TRASH', 'REJECTED']);
            $form->update([
                'manual_payment_method' => $request->input('manual_payment_method'),
                'manual_trx_id'         => $submittedTrxId,
                'manual_sender_phone'   => trim($request->input('manual_sender_phone')),
                'manual_paid_amount'    => (float) $request->input('manual_paid_amount'),
                'manual_payment_notes'  => $request->input('manual_payment_notes'),
                'manual_payment_date'   => now(),
                'status'                => 'PENDING',
                'rejection_reason'      => $wasTrashed ? null : $form->rejection_reason,
                'notes'                 => trim(($form->notes ? $form->notes . "\n" : '') . ($wasTrashed ? 'পেমেন্ট সাবমিট করায় ট্র্যাশ থেকে স্বয়ংক্রিয়ভাবে আন-ট্র্যাশ (Untrashed) করা হয়েছে।' : '')),
            ]);

            return redirect()->route('apply.success', $form->application_no)
                ->with('success', "আপনার পেমেন্ট তথ্য (TrxID: {$form->manual_trx_id}, ৳ " . number_format($form->manual_paid_amount, 2) . ") সফলভাবে জমা নেওয়া হয়েছে! কর্তৃপক্ষ ট্রাঞ্জেকশন যাচাই করে আপনার ভর্তি অনুমোদন করবে।");
        }

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

    /**
     * Self-healing check: Ensure approved application has dedicated student record with accurate Course and Gender code.
     */
    public static function ensureCourseAndGenderSpecificStudent(AdmissionForm &$form): void
    {
        $form->load('student');
        if ($form->status !== 'APPROVED' || !$form->student || !$form->interested_course_id) {
            return;
        }

        $student = $form->student;
        $batch = $form->batch ?: Batch::where('course_id', $form->interested_course_id)->where('status', 'ACTIVE')->first();
        if (!$batch) {
            return;
        }
        $targetCourse = $batch->course ?: Course::find($form->interested_course_id);
        if (!$targetCourse) {
            return;
        }

        $effectiveGender = $form->gender ?: ($student->gender ?? 'Male');
        $year = Student::resolveAcademicYearCode($batch);
        $batchNum = Student::resolveBatchNumberCode($batch);
        $courseCode = Student::resolveCourseCode($targetCourse, $targetCourse->id);
        $genderCode = Student::resolveGenderCode($effectiveGender);
        $expectedPrefix = "{$year}{$batchNum}{$courseCode}{$genderCode}";

        $cleanCode = preg_replace('/\D/', '', (string)($student->student_code ?? ''));

        // Check if student belongs to a different course
        $isDifferentCourse = false;
        if (!empty($cleanCode)) {
            $existingCourseCode = strlen($cleanCode) >= 6 ? substr($cleanCode, 4, 2) : '';
            if ($existingCourseCode !== $courseCode) {
                $isDifferentCourse = true;
            }
        }
        if ($student->enrollments()->where('course_id', '!=', $targetCourse->id)->exists()) {
            $isDifferentCourse = true;
        }
        if ($student->admissionForms()->where('id', '!=', $form->id)->where('interested_course_id', '!=', $targetCourse->id)->exists()) {
            $isDifferentCourse = true;
        }

        // Check if the current student_code prefix mismatches (e.g. wrong gender digit, wrong batch/course)
        $hasPrefixMismatch = empty($cleanCode) || strlen($cleanCode) < 7 || substr($cleanCode, 0, 7) !== $expectedPrefix;

        if ($isDifferentCourse || $hasPrefixMismatch) {
            DB::transaction(function () use ($form, $student, $batch, $targetCourse, $effectiveGender, $isDifferentCourse) {
                if ($isDifferentCourse) {
                    $newStudent = $student->replicate(['id', 'student_code', 'user_id', 'created_at', 'updated_at']);
                    $newStudent->status = 'ACTIVE';
                    $newStudent->gender = $effectiveGender;
                    $newStudent->student_code = Student::generateStudentCode($batch, $targetCourse, $effectiveGender);
                    $newStudent->save();

                    $rawPassword = $student->getOrGenerateNumericPassword();
                    $realEmail = $newStudent->email ?: ($form->email ?: null);
                    $existingUserWithRealEmail = $realEmail ? User::where('email', $realEmail)->first() : null;
                    $canUseRealEmail = $realEmail && (!$existingUserWithRealEmail || !Student::where('user_id', $existingUserWithRealEmail->id)->where('id', '!=', $newStudent->id)->exists());

                    if ($canUseRealEmail) {
                        $loginEmail = $realEmail;
                        $user = $existingUserWithRealEmail ?: User::create([
                            'name'     => $newStudent->name,
                            'email'    => $loginEmail,
                            'password' => Hash::make($rawPassword),
                            'role'     => 'student',
                        ]);
                        if ($existingUserWithRealEmail) {
                            $user->password = Hash::make($rawPassword);
                            $user->save();
                        }
                    } else {
                        $loginEmail = $newStudent->student_code . '@iom.student';
                        $user = User::firstOrCreate(
                            ['email' => $loginEmail],
                            [
                                'name'     => $newStudent->name,
                                'password' => Hash::make($rawPassword),
                                'role'     => 'student',
                            ]
                        );
                        $user->password = Hash::make($rawPassword);
                        $user->save();
                    }
                    $newStudent->user_id = $user->id;
                    $newStudent->temporary_password = $rawPassword;
                    $newStudent->save();

                    $form->update(['student_id' => $newStudent->id, 'gender' => $effectiveGender]);
                    $form->setRelation('student', $newStudent);

                    Enrollment::where('admission_form_id', $form->id)->update(['student_id' => $newStudent->id]);
                    Invoice::where('source_type', AdmissionForm::class)
                        ->where('source_id', $form->id)
                        ->update(['student_id' => $newStudent->id]);
                } else {
                    // Update existing student record with the correct gender and code
                    $student->gender = $effectiveGender;
                    $student->student_code = Student::generateStudentCode($batch, $targetCourse, $effectiveGender);
                    $student->save();

                    if ($student->user) {
                        $realEmail = $student->email ?: ($form->email ?: null);
                        if ($realEmail) {
                            $existingUserWithEmail = User::where('email', $realEmail)->where('id', '!=', $student->user->id)->first();
                            if (!$existingUserWithEmail) {
                                $student->user->update(['email' => $realEmail]);
                            }
                        } elseif (str_contains($student->user->email, '@iom.student')) {
                            $loginEmail = $student->student_code . '@iom.student';
                            $student->user->update(['email' => $loginEmail]);
                        }
                    }
                    $form->update(['gender' => $effectiveGender]);
                }
            });
            $form->refresh();
            $form->load('student');
        }
    }

    public function success(string $applicationNo)
    {
        $form = AdmissionForm::with(['interestedCourse', 'session', 'student', 'batch'])
            ->where('application_no', $applicationNo)
            ->where('source', 'PUBLIC')
            ->firstOrFail();

        self::ensureCourseAndGenderSpecificStudent($form);

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
        $selectedId = $request->query('selected_id');
        $admissions = collect();
        $admission = null;
        $isPaid = false;

        if (!empty($searchQuery)) {
            // Find ALL matching applications by Application No, Student Phone, Email, or Student ID
            $admissions = AdmissionForm::with(['student', 'interestedCourse', 'batch', 'reviewer', 'session'])
                ->where(function ($q) use ($searchQuery) {
                    $q->where('application_no', $searchQuery)
                      ->orWhereHas('student', function ($sq) use ($searchQuery) {
                          $sq->where('phone', $searchQuery)
                             ->orWhere('email', $searchQuery)
                             ->orWhere('student_code', $searchQuery);
                      });
                })
                ->latest('id')
                ->get();

            // Self-heal each approved admission to guarantee accurate course-specific roll & credentials
            foreach ($admissions as $adm) {
                if ($adm->status === 'APPROVED') {
                    self::ensureCourseAndGenderSpecificStudent($adm);
                }

                // Check payment status for each
                $adm->is_paid = GatewayTransaction::where('admission_form_id', $adm->id)
                    ->where('status', 'SUCCESS')
                    ->exists()
                    || Invoice::where('source_type', AdmissionForm::class)
                    ->where('source_id', $adm->id)
                    ->where('status', 'PAID')
                    ->exists()
                    || ($adm->interestedCourse && (float)($adm->interestedCourse->admission_fee ?? 0) == 0);
            }

            // Determine which admission to display in full detail
            if ($selectedId) {
                $admission = $admissions->firstWhere('id', (int) $selectedId);
            } elseif ($admissions->contains('application_no', $searchQuery)) {
                $admission = $admissions->firstWhere('application_no', $searchQuery);
            } elseif ($admissions->isNotEmpty()) {
                $admission = $admissions->first();
            }

            if ($admission) {
                $isPaid = $admission->is_paid ?? false;
            }
        }

        return view('apply.track', compact('admissions', 'admission', 'searchQuery', 'isPaid'));
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
