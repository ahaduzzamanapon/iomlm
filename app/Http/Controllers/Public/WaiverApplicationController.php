<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\WaiverApplication;
use Illuminate\Http\Request;

class WaiverApplicationController extends Controller
{
    public function show()
    {
        $divisions = Division::orderBy('name')->get();
        return view('public.poor_fund', compact('divisions'));
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
        ]);

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
            'convenient_admission_fee'       => $validated['convenient_admission_fee'] ?? 0,
            'convenient_monthly_fee'         => $validated['convenient_monthly_fee'] ?? 0,
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

        $app = WaiverApplication::where('application_no', $code)->first();

        if (!$app) {
            return response()->json([
                'valid'   => false,
                'message' => '✕ এই পুওর ফান্ড কোডটি ('.$code.') সঠিক নয়। (Code Not Found)'
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

        $discText = ($app->discount_type === 'FIXED')
            ? '৳' . number_format($app->approved_discount_value, 0) . ' নির্দিষ্ট ছাড় (Fixed Discount)'
            : ($app->approved_discount_value > 0 ? $app->approved_discount_value : $app->approved_discount_percent) . '% ছাড় (Percentage Waiver)';

        // APPROVED & NOT USED!
        return response()->json([
            'valid'                     => true,
            'status'                    => 'APPROVED',
            'application_no'            => $app->application_no,
            'discount_type'             => $app->discount_type ?? 'PERCENTAGE',
            'approved_discount_value'   => $app->approved_discount_value ?? $app->approved_discount_percent,
            'approved_discount_percent' => $app->approved_discount_percent,
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
            'message'                   => "✓ অভিনন্দন! আপনার পুওর ফান্ড কোডটি অনুমোদিত (Approved)। আপনার জন্য {$discText} এবং ফর্মের তথ্যগুলো অটো-ফিল করা হয়েছে।",
        ]);
    }
}
