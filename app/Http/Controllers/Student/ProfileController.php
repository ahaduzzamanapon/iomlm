<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\BloodGroup;
use App\Models\Religion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('user_id', auth()->id())
            ->orWhere('email', auth()->user()->email)
            ->first();
    }

    public function index()
    {
        $student = $this->student();

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'শিক্ষার্থীর তথ্য খুঁজে পাওয়া যায়নি।');
        }

        $percent = $student->calculateProfileCompletion();
        $missing = $student->getMissingProfileFields();

        $bloodGroups = class_exists(BloodGroup::class) ? BloodGroup::where('is_active', true)->get() : collect();
        $religions   = class_exists(Religion::class) ? Religion::where('is_active', true)->get() : collect();

        return view('student.profile.index', compact('student', 'percent', 'missing', 'bloodGroups', 'religions'));
    }

    public function update(Request $request)
    {
        $student = $this->student();

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'শিক্ষার্থীর প্রোফাইল খুঁজে পাওয়া যায়নি।');
        }

        $validated = $request->validate([
            // Personal
            'date_of_birth'           => 'nullable|date',
            'blood_group'             => 'nullable|string|max:10',
            'national_id'             => 'nullable|string|max:50',
            'nationality'             => 'nullable|string|max:50',
            'religion'                => 'nullable|string|max:50',
            'photo'                   => 'nullable|image|max:2048',

            // Guardian
            'father_name'             => 'nullable|string|max:200',
            'mother_name'             => 'nullable|string|max:200',
            'guardian_name'           => 'nullable|string|max:200',
            'guardian_phone'          => 'nullable|string|max:30',
            'guardian_relation'       => 'nullable|string|max:50',

            // Address
            'address'                 => 'nullable|string|max:500',
            'permanent_address'       => 'nullable|string|max:500',

            // Education
            'occupation'              => 'nullable|string|max:100',
            'education_qualification' => 'nullable|string|max:100',
            'ssc_school'              => 'nullable|string|max:200',
            'ssc_board'               => 'nullable|string|max:100',
            'ssc_year'                => 'nullable|integer|min:1980|max:' . now()->year,
            'ssc_gpa'                 => 'nullable|numeric|min:0|max:5',
            'hsc_college'             => 'nullable|string|max:200',
            'hsc_board'               => 'nullable|string|max:100',
            'hsc_year'                => 'nullable|integer|min:1980|max:' . now()->year,
            'hsc_gpa'                 => 'nullable|numeric|min:0|max:5',
            'university_name'         => 'nullable|string|max:200',
            'department_name'         => 'nullable|string|max:100',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/students', 'public');
            $validated['photo_url'] = '/storage/' . $path;
        }

        unset($validated['photo']);

        $student->update($validated);

        // Recalculate completion score
        $newPercent = $student->calculateProfileCompletion();

        if ($student->isProfileCompleted()) {
            return redirect()->route('student.dashboard')
                ->with('success', "মাশাআল্লাহ! আপনার প্রোফাইল সফলভাবে {$newPercent}% সম্পন্ন হয়েছে। এখন স্টুডেন্ট পোর্টালের সকল ফিচার আপনার জন্য উন্মুক্ত।");
        }

        return redirect()->route('student.profile.index')
            ->with('info', "আপনার তথ্য হালনাগাদ করা হয়েছে (বর্তমান অগ্রগতি: {$newPercent}%)। পোর্টালের অন্যান্য সুবিধাদি উন্মুক্ত করতে অনুগ্রহ করে বাকি ক্ষেত্রগুলোও পূরণ করে ৯৫% সম্পন্ন করুন।");
    }
}
