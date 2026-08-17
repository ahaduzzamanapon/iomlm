<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AdmissionForm;
use App\Models\AppSetting;
use App\Models\BloodGroup;
use App\Models\Course;
use App\Models\District;
use App\Models\Division;
use App\Models\Religion;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionFormController extends Controller
{
    public function show()
    {
        $terms         = AppSetting::get('admission_terms', '');
        $courses       = Course::where('is_active', true)->orderBy('name')->get();
        $activeBatches = \App\Models\Batch::where('status', 'ACTIVE')->with('course')->get();
        $sessions      = AcademicSession::where('is_active', true)->orderByDesc('id')->get();
        $bloodGroups   = BloodGroup::active()->get();
        $religions     = Religion::active()->get();
        $divisions     = Division::orderBy('name')->get();

        return view('apply.index', compact(
            'terms', 'courses', 'activeBatches', 'sessions', 'bloodGroups', 'religions', 'divisions'
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
            'date_of_birth'           => 'nullable|date',
            'occupation'              => 'nullable|string|max:100',
            'education_qualification' => 'nullable|string|max:100',
            'ssc_school'              => 'nullable|string|max:200',
            'ssc_board'               => 'nullable|string|max:100',
            'ssc_year'                => 'nullable|integer|min:1990|max:' . now()->year,
            'hsc_college'             => 'nullable|string|max:200',
            'hsc_board'               => 'nullable|string|max:100',
            'hsc_year'                => 'nullable|integer|min:1990|max:' . now()->year,
            'university_name'         => 'nullable|string|max:200',
            'department_name'         => 'nullable|string|max:100',
            'device_type'             => 'nullable|string|max:50',
            'gender'                  => 'nullable|in:Male,Female,Other',
            'blood_group_id'          => 'nullable|exists:blood_groups,id',
            'email'                   => 'nullable|email|max:150',
            'national_id'             => 'nullable|string|max:50',
            'passport_no'             => 'nullable|string|max:50',
            'birth_certificate_no'    => 'nullable|string|max:50',
            'nationality'             => 'nullable|string|max:50',
            'religion_id'             => 'nullable|exists:religions,id',
            'present_house'           => 'nullable|string|max:300',
            'present_post_office'     => 'nullable|string|max:100',
            'present_police_station'  => 'nullable|string|max:100',
            'present_district_id'     => 'nullable|exists:districts,id',
            'present_division_id'     => 'nullable|exists:divisions,id',
            'same_as_present'         => 'nullable|boolean',
            'permanent_house'         => 'nullable|string|max:300',
            'permanent_post_office'   => 'nullable|string|max:100',
            'permanent_police_station'=> 'nullable|string|max:100',
            'permanent_district_id'   => 'nullable|exists:districts,id',
            'permanent_division_id'   => 'nullable|exists:divisions,id',
            'waiver_code'             => 'nullable|string|max:50',
        ]);

        $form = DB::transaction(function () use ($validated, $request) {
            $sameAsPresent = $request->boolean('same_as_present');

            // Session fallback
            $sessionId = $validated['academic_session_id'] ?? AcademicSession::where('is_active', true)->orderByDesc('id')->value('id');

            // Check Waiver / Poor Fund Code
            $discountPercent = 0;
            $waiverCode = null;
            $waiverApp = null;
            if (!empty($validated['waiver_code'])) {
                $code = strtoupper(trim($validated['waiver_code']));
                $waiverApp = \App\Models\WaiverApplication::where('application_no', $code)->first();
                if ($waiverApp && $waiverApp->status === 'APPROVED' && !$waiverApp->is_used) {
                    $discountPercent = $waiverApp->approved_discount_percent;
                    $waiverCode = $code;
                }
            }

            // Create a LEAD student record
            $student = Student::create([
                'name'          => $validated['applicant_name'],
                'phone'         => $validated['phone'],
                'email'         => $validated['email'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender'        => $validated['gender'] ?? null,
                'national_id'   => $validated['national_id'] ?? null,
                'status'        => 'LEAD',
            ]);

            // Create AdmissionForm with source=PUBLIC
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
                'status'                  => 'PENDING',

                // Education
                'occupation'              => $validated['occupation'] ?? null,
                'education_qualification' => $validated['education_qualification'] ?? null,
                'ssc_school'              => $validated['ssc_school'] ?? null,
                'ssc_board'               => $validated['ssc_board'] ?? null,
                'ssc_year'                => $validated['ssc_year'] ?? null,
                'hsc_college'             => $validated['hsc_college'] ?? null,
                'hsc_board'               => $validated['hsc_board'] ?? null,
                'hsc_year'                => $validated['hsc_year'] ?? null,
                'university_name'         => $validated['university_name'] ?? null,
                'department_name'         => $validated['department_name'] ?? null,
                'device_type'             => $validated['device_type'] ?? null,

                // Personal
                'blood_group_id'          => $validated['blood_group_id'] ?? null,
                'passport_no'             => $validated['passport_no'] ?? null,
                'birth_certificate_no'    => $validated['birth_certificate_no'] ?? null,
                'nationality'             => $validated['nationality'] ?? 'Bangladeshi',
                'religion_id'             => $validated['religion_id'] ?? null,

                // Present Address
                'present_house'           => $validated['present_house'] ?? null,
                'present_post_office'     => $validated['present_post_office'] ?? null,
                'present_police_station'  => $validated['present_police_station'] ?? null,
                'present_district_id'     => $validated['present_district_id'] ?? null,
                'present_division_id'     => $validated['present_division_id'] ?? null,

                // Permanent Address
                'same_as_present'         => $sameAsPresent,
                'permanent_house'         => $sameAsPresent ? ($validated['present_house'] ?? null)          : ($validated['permanent_house'] ?? null),
                'permanent_post_office'   => $sameAsPresent ? ($validated['present_post_office'] ?? null)    : ($validated['permanent_post_office'] ?? null),
                'permanent_police_station'=> $sameAsPresent ? ($validated['present_police_station'] ?? null) : ($validated['permanent_police_station'] ?? null),
                'permanent_district_id'   => $sameAsPresent ? ($validated['present_district_id'] ?? null)    : ($validated['permanent_district_id'] ?? null),
                'permanent_division_id'   => $sameAsPresent ? ($validated['present_division_id'] ?? null)    : ($validated['permanent_division_id'] ?? null),

                'ip_address'              => $request->ip(),
            ]);

            // Mark waiver application as USED if applicable
            if ($waiverApp) {
                $waiverApp->update([
                    'is_used'           => true,
                    'admission_form_id' => $form->id,
                ]);
            }

            return $form;
        });

        return redirect()->route('apply.success', $form->application_no);
    }

    public function success(string $applicationNo)
    {
        $form = AdmissionForm::with(['interestedCourse', 'session', 'student'])
            ->where('application_no', $applicationNo)
            ->where('source', 'PUBLIC')
            ->firstOrFail();

        $instituteName    = AppSetting::get('institute_name', 'IOM');
        $instituteTagline = AppSetting::get('institute_tagline', '');

        return view('apply.success', compact('form', 'instituteName', 'instituteTagline'));
    }

    // AJAX: districts by division
    public function districts(Request $request)
    {
        $districts = District::where('division_id', $request->query('division_id'))
            ->orderBy('name')->get(['id', 'name']);
        return response()->json($districts);
    }
}
