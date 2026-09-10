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
        $terms         = AppSetting::get('admission_terms', '');
        $courses       = Course::where('is_active', true)->orderBy('name')->get();
        $activeBatches = Batch::where('status', 'ACTIVE')->with('course')->get();
        $sessions      = AcademicSession::where('is_active', true)->orderByDesc('id')->get();
        $bloodGroups   = BloodGroup::active()->get();
        $religions     = Religion::active()->get();
        $divisions     = Division::orderBy('name')->get();

        $sslActive     = PaymentGatewayService::isSslcommerzActive();
        $bkashActive   = PaymentGatewayService::isBkashActive();

        return view('apply.index', compact(
            'terms', 'courses', 'activeBatches', 'sessions', 'bloodGroups', 'religions', 'divisions',
            'sslActive', 'bkashActive'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'               => 'required|exists:courses,id',
            'batch_id'                => 'nullable|exists:batches,id',
            'academic_session_id'     => 'nullable|exists:academic_sessions,id',
            'applicant_name'          => 'required|string|max:200',
            'phone'                   => 'required|string|max:30',
            'email'                   => 'required|email|max:150',
            'gender'                  => 'required|in:Male,Female,Other,male,female,other',
            'waiver_code'             => 'nullable|string|max:50',
            'payment_gateway'         => 'nullable|in:sslcommerz,bkash',
            'terms_agreed'            => 'nullable',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $batch  = !empty($validated['batch_id']) ? Batch::find($validated['batch_id']) : null;

        // Calculate Admission Fee
        $courseFee = (float) ($course->admission_fee ?? 0);
        $batchFee  = ($batch && $batch->admission_fee !== null) ? (float) $batch->admission_fee : $courseFee;
        $baseFee   = max(0, $batchFee);

        // Check Waiver / Coupon Code
        $discountAmount  = 0.0;
        $discountPercent = 0.0;
        $waiverCode      = null;
        $waiverApp       = null;

        if (!empty($validated['waiver_code'])) {
            $code = strtoupper(trim($validated['waiver_code']));
            $waiverApp = WaiverApplication::where('application_no', $code)
                ->where('status', 'APPROVED')
                ->where('is_used', false)
                ->first();

            if ($waiverApp) {
                $waiverCode = $code;
                if ($waiverApp->approved_admission_fee !== null && in_array($waiverApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
                    $approvedFee    = (float) $waiverApp->approved_admission_fee;
                    $payableAmount  = min($baseFee, $approvedFee);
                    $discountAmount = max(0, $baseFee - $payableAmount);
                } else {
                    $discountPercent = (float) ($waiverApp->approved_discount_percent ?? 0);
                    $discountAmount  = round(($baseFee * $discountPercent) / 100, 2);
                }
            }
        }

        $netPayable = max(0, round($baseFee - $discountAmount, 2));

        // Create Application & Lead Student inside Transaction
        $result = DB::transaction(function () use ($validated, $request, $waiverCode, $discountPercent, $discountAmount, $netPayable, $waiverApp) {
            $sameAsPresent = $request->boolean('same_as_present');
            $sessionId     = $validated['academic_session_id']
                ?? AcademicSession::where('is_active', true)->orderByDesc('id')->value('id');

            // 1. Create Student as LEAD
            $student = Student::create([
                'name'          => $validated['applicant_name'],
                'phone'         => $validated['phone'],
                'email'         => $validated['email'] ?? null,
                'gender'        => $validated['gender'] ?? null,
                'status'        => 'LEAD',
            ]);

            $student->calculateProfileCompletion();

            // 2. Create AdmissionForm
            $form = AdmissionForm::create([
                'source'                  => 'PUBLIC',
                'application_no'          => AdmissionForm::generateApplicationNo(),
                'student_id'              => $student->id,
                'interested_course_id'    => $validated['course_id'],
                'batch_id'                => $validated['batch_id'] ?? null,
                'academic_session_id'     => $sessionId,
                'attempt_no'              => 1,
                'lead_source'             => 'Website',
                'waiver_code'             => $waiverCode,
                'discount_percent'        => $discountPercent,
                'discount_amount'         => $discountAmount,
                'status'                  => 'PENDING',
                'ip_address'              => $request->ip(),
            ]);

            if ($waiverApp) {
                $waiverApp->update([
                    'is_used'           => true,
                    'admission_form_id' => $form->id,
                ]);
            }

            return ['form' => $form, 'student' => $student];
        });

        $form    = $result['form'];
        $student = $result['student'];

        // ── ONLINE PAYMENT WORKFLOW ──────────────────────────────────────
        $chosenGateway = $validated['payment_gateway'] ?? null;
        $sslActive     = PaymentGatewayService::isSslcommerzActive();
        $bkashActive   = PaymentGatewayService::isBkashActive();

        if ($netPayable > 0 && ($sslActive || $bkashActive)) {
            // Default gateway fallback if student didn't explicitly pick one
            if (empty($chosenGateway)) {
                $chosenGateway = $sslActive ? 'sslcommerz' : 'bkash';
            }

            $gatewayMode = ($chosenGateway === 'bkash')
                ? PaymentGatewayService::getBkashConfig()['mode']
                : PaymentGatewayService::getSslcommerzConfig()['mode'];

            // 1. Create Gateway Transaction Record
            $transaction = GatewayTransaction::create([
                'tran_id'           => GatewayTransaction::generateTranId('ADM'),
                'gateway'           => $chosenGateway,
                'gateway_mode'      => $gatewayMode,
                'admission_form_id' => $form->id,
                'student_id'        => $student->id,
                'amount'            => $netPayable,
                'currency'          => 'BDT',
                'customer_name'     => $student->name,
                'customer_phone'    => $student->phone,
                'customer_email'    => $student->email,
                'status'            => 'INITIATED',
                'ip_address'        => $request->ip(),
            ]);

            // 2. Initiate Payment Session with Gateway
            if ($chosenGateway === 'sslcommerz') {
                $initRes = PaymentGatewayService::initiateSslcommerz(
                    $transaction, $form, $student->phone, $student->email, $student->name
                );
            } else {
                $initRes = PaymentGatewayService::initiateBkash(
                    $transaction, $form, $student->phone
                );
            }

            if (!empty($initRes['success']) && !empty($initRes['redirect_url'])) {
                return redirect()->away($initRes['redirect_url']);
            }

            // If initiation failed, redirect to status screen with error details
            Log::error("Payment Initiation Failed for {$chosenGateway}: " . ($initRes['message'] ?? ''));
            return redirect()->route('payment.status', $transaction->tran_id)
                ->with('error', $initRes['message'] ?? 'পেমেন্ট গেটওয়েতে সংযোগ করতে ত্রুটি হয়েছে।');
        }

        // ── FREE ADMISSION (Net Payable is 0.00) ──────────────────────────
        return redirect()->route('apply.success', $form->application_no)
            ->with('success', 'আপনার ভর্তি আবেদন সফলভাবে জমা হয়েছে।');
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

        $instituteName    = AppSetting::get('institute_name', 'Islamic Online Madrasah');
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
}
